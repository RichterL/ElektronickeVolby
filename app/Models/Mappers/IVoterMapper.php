<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\VoterFile;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IVoterMapper
{
	public function importFromFile(Election $election, VoterFile $voterFile): bool;

	public function deleteRelated(Election $election): bool;

	public function getDataSource(array $filter = []): IDataSource;
}
