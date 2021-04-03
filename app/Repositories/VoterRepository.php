<?php
declare(strict_types=1);

namespace Repositories;

use Models\Entities\Election\Election;
use Models\Entities\Election\VoterFile;
use Models\Mappers\IVoterMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class VoterRepository
{
	private IVoterMapper $voterMapper;

	public function __construct(IVoterMapper $voterMapper)
	{
		$this->voterMapper = $voterMapper;
	}

	public function importFromFile(Election $election, VoterFile $voterFile): bool
	{
		return $this->voterMapper->importFromFile($election, $voterFile);
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->voterMapper->getDataSource($filter);
	}
}
