<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use Exception;
use Models\Entities\Election\Election;
use Models\Entities\Election\VoterFile;
use Models\Entities\Entity;
use Models\Mappers\IVoterMapper;

class VoterMapper extends BaseMapper implements IVoterMapper
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

	public function create(array $data = []): Entity
	{
		return null;
	}

	private function prepareFile(Election $election, VoterFile $voterFile)
	{
		$content = $voterFile->getContent();
		$now = new \DateTime();
		if (!is_dir(TEMP_DIR . '/voterFiles/' . $election->getId())) {
			mkdir(TEMP_DIR . '/voterFiles/' . $election->getId(), 0777, true);
		}
		$filename = TEMP_DIR . '/voterFiles/' . $election->getId() . '/' . $now->format('dmy_Hi') . '_' . $voterFile->filename;
		if (!file_put_contents($filename, $content)) {
			throw new \RuntimeException('Saving temporary file failed!');
		}
		return $filename;
	}
}
