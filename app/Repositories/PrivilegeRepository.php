<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Resource\Privilege;
use Models\Entities\Resource\Resource;
use Models\Mappers\Db\PrivilegeMapper;

class PrivilegeRepository
{
	private PrivilegeMapper $privilegeMapper;

	public function __construct(PrivilegeMapper $privilegeMapper)
	{
		$this->privilegeMapper = $privilegeMapper;
	}

	public function findById(int $privilegeId): ?Privilege
	{
		return $this->privilegeMapper->findOne(['id' => $privilegeId]);
	}

	public function findByResource(Resource $resource): array
	{
		return $this->privilegeMapper->findRelated($resource);
	}

	public function save(Resource $resource, Privilege $privilege)
	{
		return $this->privilegeMapper->save($resource, $privilege);
	}
}
