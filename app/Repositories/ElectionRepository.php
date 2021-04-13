<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Election\Election;
use App\Models\Entities\User;
use App\Models\Mappers\ElectionMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class ElectionRepository
{
	private ElectionMapper $electionMapper;

	public function __construct(ElectionMapper $electionMapper)
	{
		$this->electionMapper = $electionMapper;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id): Election
	{
		return $this->electionMapper->findOne(['id' => $id]);
	}

	/** @return Election[] */
	public function findActive(): array
	{
		return $this->electionMapper->find(['active' => true]);
	}

	public function findRelated(User $user): array
	{
		return $this->electionMapper->findRelated($user);
	}

	/** @return Election[] */
	public function findAll(): array
	{
		return $this->electionMapper->findAll();
	}

	public function getDataSource(): IDataSource
	{
		return $this->electionMapper->getDataSource();
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Election $election): bool
	{
		return $this->electionMapper->save($election);
	}

	public function delete(Election $election): bool
	{
		return $this->electionMapper->delete($election);
	}
}
