<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Role\Role;
use App\Models\Entities\Rule\Rule;
use App\Models\Entities\Rule\RuleCollection;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\RuleMapper;
use Ublaboo\DataGrid\DataSource\IDataSource;

class RuleRepository
{
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
	public function save(Rule $rule): bool
	{
		return $this->ruleMapper->save($rule);
	}

	public function delete(Rule $rule): bool
	{
		return $this->ruleMapper->delete($rule);
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->ruleMapper->getDataSource($filter);
	}
}
