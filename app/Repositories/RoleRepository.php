<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Role\Role;
use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\RoleMapper;
use App\Models\Mappers\RuleMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class RoleRepository extends BaseRepository
{
	public const CACHE_NAMESPACE = 'roles';

	private RoleMapper $roleMapper;
	private RuleMapper $ruleMapper;

	public function __construct(RoleMapper $roleMapper, RuleMapper $ruleMapper)
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
		return $this->cache->load('findAll', function () use ($includeRules) {
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

	public function getIdNamePairs(): array
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

	/**
	 * @throws DeletingErrorException
	 */
	public function delete(Role $role): bool
	{
		return $this->roleMapper->delete($role);
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->roleMapper->getDataSource($filter);
	}
}
