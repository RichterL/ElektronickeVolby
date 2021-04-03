<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Election\Election;
use Models\Entities\Election\VoterFile;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IVoterMapper
{
	public function importFromFile(Election $election, VoterFile $voterFile): bool;

	public function deleteRelated(Election $election): bool;

	public function getDataSource(array $filter = []): IDataSource;
}
