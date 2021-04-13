<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Role\Role;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\IRoleMapper;
use App\Models\Mappers\IRuleMapper;
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

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id, bool $includeRules = false): Role
	{
		$role = $this->roleMapper->findOne(['id' => $id]);
		if ($includeRules) {
			$rules = $this->ruleMapper->findRelated($role);
			$role->addRules($rules);
		}
		return $role;
	}

	/** @return Role[] */
	public function findByKey(string $key): array
	{
		return $this->roleMapper->find(['key' => $key]);
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

	/**
	 * @throws SavingErrorException
	 */
	public function save(Role $role): bool
	{
		return $this->roleMapper->save($role);
	}

	public function delete(Role $role): bool
	{
		return $this->roleMapper->delete($role);
	}

	public function getDataSource(): IDataSource
	{
		return $this->roleMapper->getDataSource();
	}
}
