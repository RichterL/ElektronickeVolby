<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Election\Election;
use Models\Entities\Election\VoterFile;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IVoterFileMapper
{
	public function save(VoterFile $voterFile): bool;

	public function delete(VoterFile $voterFile): bool;

	/** @return VoterFile[]|null */
	public function findRelated(Election $election): ?array;

	public function getDataSource(array $filter = []): IDataSource;

	public function findOne(array $filter = []): ?VoterFile;

	/** @return VoterFile[] */
	public function findAll(): array;
}
