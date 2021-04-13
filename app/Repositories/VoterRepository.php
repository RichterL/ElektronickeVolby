<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\VoterFile;
use App\Models\Mappers\IVoterMapper;
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
