<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Role\Role;
use Models\Mappers\RoleMapper;

class RoleRepository
{
	private RoleMapper $roleMapper;

	public function __construct(RoleMapper $roleMapper)
	{
		$this->roleMapper = $roleMapper;
	}

	public function findById(int $id): ?Role
	{
		return $this->roleMapper->findOne(['id' => $id]);
	}

	public function findAll()
	{
		return $this->roleMapper->findAll();
	}

	public function getIdNamePairs()
	{
		$roles = $this->roleMapper->findAll();
		$pairs = [];
		foreach ($roles as $role) {
			$pairs[$role->id] = $role->name;
		}
		return $pairs;
	}

	public function save(Role $role): bool
	{
		return $this->roleMapper->save($role);
	}
}
