<?php
declare(strict_types=1);

namespace App\Models\Mappers\Db;

use App\Models\Entities\Election\ElectionId;
use App\Models\Entities\User;
use App\Models\Mappers\Exception\SavingErrorException;
use Exception;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\VoterFile;
//use App\Models\Entities\Entity;
use App\Models\Mappers\VoterMapper;

class VoterDbMapper extends BaseDbMapper implements VoterMapper
{
	protected string $table = Tables::VOTER;

	/**
	 * requires mysql to run with --secure-file-priv="" or secure-file-priv="" in config
	 */
	public function importFromFile(Election $election, VoterFile $voterFile): bool
	{
		$this->dibi->begin();
		try {
			$this->deleteRelated($election);

			$filename = $this->prepareFile($election, $voterFile);
			$this->dibi->query('%SQL', "
				LOAD DATA INFILE '{$filename}' INTO TABLE {$this->table}
				FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
				LINES TERMINATED BY '\\n'
				(@dummy, @dummy, email)
				SET election_id = {$election->getId()}
			");
			$this->dibi->commit();
		} catch (\Dibi\Exception $ex) {
			$this->dibi->rollback();
			throw new Exception('Unable to load new voter list: ' . $ex->getMessage());
		}
		return true;
	}

	public function deleteRelated(Election $election): bool
	{
		return (bool) $this->dibi->delete($this->table)->where('election_id = %i', $election->getId())->execute();
	}

//	protected function create(array $data = []): Entity
//	{
//		return null;
//	}

	public function getCount(Election $election, bool $voted = false): int
	{
		$select = $this->dibi->select('COUNT(id)')
			->from($this->table)
			->where('election_id = %i', $election->getId());
		if ($voted) {
			$select->where('timestamp IS NOT NULL');
		}
		return (int) $select->groupBy('election_id')->fetchSingle();
	}

	private function prepareFile(Election $election, VoterFile $voterFile): string
	{
		$content = $voterFile->getContent();
		$now = new \DateTime();
		if (!is_dir(TEMP_DIR . '/voterFiles/' . $election->getId())
			&& !mkdir($concurrentDirectory = TEMP_DIR . '/voterFiles/' . $election->getId(), 0777, true)
			&& !is_dir($concurrentDirectory) // race-condition fix
		) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		$filename = TEMP_DIR . '/voterFiles/' . $election->getId() . '/' . $now->format('dmy_Hi') . '_' . $voterFile->filename;
		if (!file_put_contents($filename, $content)) {
			throw new \RuntimeException('Saving temporary file failed!');
		}
		return $filename;
	}

	/**
	 * @throws SavingErrorException
	 */
	public function update(User $user, ElectionId $electionId): void
	{
		try {
			$this->dibi->update($this->table, ['timestamp' => new \DateTime()])
				->where('election_id = %i', $electionId->getValue())
				->where('email LIKE %like', $user->getEmail())
				->execute();
		} catch (\Dibi\Exception $e) {
			throw new SavingErrorException('unable to update voter list');
		}
	}

	public function hasVoted(User $user, Election $election): bool
	{
		return (bool) $this->dibi->select('voted')
			->from($this->table)
			->where('election_id = %i', $election->getId())
			->where('email LIKE %like', $user->getEmail())
			->fetchSingle();
	}
}
