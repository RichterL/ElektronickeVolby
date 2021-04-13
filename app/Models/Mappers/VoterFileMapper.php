<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\VoterFile;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface VoterFileMapper
{
	/**
	 * @throws SavingErrorException
	 */
	public function save(VoterFile $voterFile): bool;

	public function delete(VoterFile $voterFile): bool;

	/** @return VoterFile[]|null */
	public function findRelated(Election $election): ?array;

	public function getDataSource(array $filter = []): IDataSource;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): VoterFile;

	/** @return VoterFile[] */
	public function findAll(): array;
}
