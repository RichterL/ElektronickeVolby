<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Role\Role;
use App\Models\Entities\Rule\Rule;
use App\Models\Entities\Rule\RuleCollection;
use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\RuleMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class RuleRepository extends BaseRepository
{
	public const CACHE_NAMESPACE = 'rules';
	private RuleMapper $ruleMapper;

	public function __construct(RuleMapper $ruleMapper)
	{
		$this->ruleMapper = $ruleMapper;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id): Rule
	{
		return $this->ruleMapper->findOne(['id' => $id]);
	}

	public function findAll(): array
	{
		return $this->ruleMapper->findAll();
	}

	public function findRelated(Role $role): RuleCollection
	{
		return $this->ruleMapper->findRelated($role);
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(Rule $rule): void
	{
		$this->ruleMapper->save($rule);
		$this->invalidate([RoleRepository::CACHE_NAMESPACE, self::CACHE_NAMESPACE]);
	}

	/**
	 * @throws DeletingErrorException
	 */
	public function delete(Rule $rule): void
	{
		$this->ruleMapper->delete($rule);
		$this->invalidate([RoleRepository::CACHE_NAMESPACE, self::CACHE_NAMESPACE]);
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->ruleMapper->getDataSource($filter);
	}
}
