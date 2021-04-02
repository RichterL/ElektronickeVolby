<?php
declare(strict_types=1);

namespace Repositories;

use Models\Entities\Election\Election;
use Models\Entities\Election\VoterFile;
use Models\Mappers\IVoterFileMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class VoterFileRepository
{
	private IVoterFileMapper $voterFileMapper;

	public function __construct(IVoterFileMapper $voterFileMapper)
	{
		$this->voterFileMapper = $voterFileMapper;
	}

	public function findById(int $id): ?VoterFile
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

	public function save(VoterFile $voterFile): bool
	{
		return $this->voterFileMapper->save($voterFile);
	}

	public function delete(VoterFile $voterFile): bool
	{
		return $this->voterFileMapper->delete($voterFile);
	}
}
