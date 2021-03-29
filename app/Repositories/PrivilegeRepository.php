<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Resource\Privilege;
use Models\Entities\Resource\PrivilegeCollection;
use Models\Entities\Resource\Resource;
use Models\Mappers\IPrivilegeMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class PrivilegeRepository
{
	private IPrivilegeMapper $privilegeMapper;

	public function __construct(IPrivilegeMapper $privilegeMapper)
	{
		$this->privilegeMapper = $privilegeMapper;
	}

	public function findById(int $privilegeId): ?Privilege
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
