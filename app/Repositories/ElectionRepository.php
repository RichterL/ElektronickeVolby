<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Election\Election;
use Models\Mappers\IElectionMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class ElectionRepository
{
	private IElectionMapper $electionMapper;

	public function __construct(IElectionMapper $electionMapper)
	{
		$this->electionMapper = $electionMapper;
	}

	public function findById(int $id): ?Election
	{
		return $this->electionMapper->findOne(['id' => $id]);
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

	public function save(Election $election)
	{
		return $this->electionMapper->save($election);
	}

	public function delete(Election $election)
	{
		return $this->electionMapper->delete($election);
	}
}
