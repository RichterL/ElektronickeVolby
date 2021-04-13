<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\VoterFile;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\VoterFileMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class VoterFileRepository
{
	private VoterFileMapper $voterFileMapper;

	public function __construct(VoterFileMapper $voterFileMapper)
	{
		$this->voterFileMapper = $voterFileMapper;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id): VoterFile
	{
		return $this->voterFileMapper->findOne(['id' => $id]);
	}

	/** @return VoterFile[]|null */
	public function findRelated(Election $election): ?array
	{
		return $this->voterFileMapper->findRelated($election);
	}

	public function findAll(): array
	{
		return $this->voterFileMapper->findAll();
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->voterFileMapper->getDataSource($filter);
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(VoterFile $voterFile): bool
	{
		return $this->voterFileMapper->save($voterFile);
	}

	public function delete(VoterFile $voterFile): bool
	{
		return $this->voterFileMapper->delete($voterFile);
	}
}
