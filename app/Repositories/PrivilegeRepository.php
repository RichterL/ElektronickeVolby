<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Resource\Privilege;
use App\Models\Entities\Resource\PrivilegeCollection;
use App\Models\Entities\Resource\Resource;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\PrivilegeMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class PrivilegeRepository
{
	private PrivilegeMapper $privilegeMapper;

	public function __construct(PrivilegeMapper $privilegeMapper)
	{
		$this->privilegeMapper = $privilegeMapper;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $privilegeId): Privilege
	{
		return $this->privilegeMapper->findOne(['id' => $privilegeId]);
	}

	public function findAll(): PrivilegeCollection
	{
		return $this->privilegeMapper->findAll();
	}

	public function findByResource(Resource $resource): PrivilegeCollection
	{
		return $this->privilegeMapper->findRelated($resource);
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Resource $resource, Privilege $privilege)
	{
		return $this->privilegeMapper->save($resource, $privilege);
	}

	public function delete(Privilege $privilege): bool
	{
		return $this->privilegeMapper->delete($privilege);
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->privilegeMapper->getDataSource($filter);
	}
}
