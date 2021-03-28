<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Role\Role;
use Models\Mappers\IRoleMapper;
use Models\Mappers\IRuleMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class RoleRepository extends BaseRepository
{
	private IRoleMapper $roleMapper;
	private IRuleMapper $ruleMapper;

	public function __construct(IRoleMapper $roleMapper, IRuleMapper $ruleMapper)
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

	public function findAll(bool $includeRules = false)
	{
		return $this->cache->load('role.findAll', function () use ($includeRules) {
			$roles = $this->roleMapper->findAll();
			if ($includeRules) {
				foreach ($roles as $role) {
					$rules = $this->ruleMapper->findRelated($role);
					$role->addRules($rules);
				}
			}
			return $roles;
		});
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

	public function getDataSource(): IDataSource
	{
		return $this->roleMapper->getDataSource();
	}
}
