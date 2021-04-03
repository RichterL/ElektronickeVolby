<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use Models\Entities\Election\Election;
use Models\Entities\Election\VoterFile;
use Models\Entities\Entity;
use Models\Mappers\IVoterMapper;

class VoterMapper extends BaseMapper implements IVoterMapper
{
	protected $table = Tables::VOTER;

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
		}

		return true;
	}

	public function deleteRelated(Election $election): bool
	{
		return (bool) $this->dibi->delete($this->table)->where('election_id = %i', $election->getId())->execute();
	}

	public function create(array $data = []): Entity
	{
		return new Entity();
	}

	private function prepareFile(Election $election, VoterFile $voterFile)
	{
		$content = $voterFile->getContent();
		$now = new \DateTime();
		if (!is_dir('/tmp/volby/voterFiles/' . $election->getId())) {
			mkdir('/tmp/volby/voterFiles/' . $election->getId());
		}
		$filename = '/tmp/volby/voterFiles/' . $election->getId() . '/' . $now->format('dmy_Hi') . '_' . $voterFile->filename;
		if (!file_put_contents($filename, $content)) {
			throw new \RuntimeException('Saving temporary file failed!');
		}
		return $filename;
	}
}
