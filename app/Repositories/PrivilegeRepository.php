<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Resource\Privilege;
use Models\Entities\Resource\PrivilegeCollection;
use Models\Entities\Resource\Resource;
use Models\Mappers\IPrivilegeMapper;

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
}
