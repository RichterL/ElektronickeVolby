<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\Role\Role;
use Models\Entities\Rule\Rule;
use Models\Entities\Rule\RuleCollection;
use Models\Mappers\IRuleMapper;

class RuleRepository
{
	private IRuleMapper $ruleMapper;

	public function __construct(IRuleMapper $ruleMapper)
	{
		$this->ruleMapper = $ruleMapper;
	}

	public function findById(int $id): ?Rule
	{
		return $this->ruleMapper->findOne(['id' => $id]);
	}

	public function findAll()
	{
		return $this->ruleMapper->findAll();
	}

	public function findRelated(Role $role): RuleCollection
	{
		return $this->ruleMapper->findRelated($role);
	}

	public function save(Rule $rule): bool
	{
		return $this->ruleMapper->save($rule);
	}
}
