<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Role\Role;
use Models\Mappers\IRuleMapper;
use Models\Mappers\RoleMapper;

class RoleRepository
{
	private RoleMapper $roleMapper;
	private IRuleMapper $ruleMapper;

	public function __construct(RoleMapper $roleMapper, IRuleMapper $ruleMapper)
	{
		$this->roleMapper = $roleMapper;
		$this->ruleMapper = $ruleMapper;
	}

	public function findById(int $id, bool $includeRules = false): ?Role
	{
		$role = $this->roleMapper->findOne(['id' => $id]);
		if ($role && $includeRules) {
			$rules = $this->ruleMapper->findRelated($role);
			$role->addRules($rules);
		}
		return $role;
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
