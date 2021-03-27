<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Role\Role;
use Models\Entities\Rule\Rule;
use Models\Entities\Rule\RuleCollection;

interface IRuleMapper
{
	public function create(array $data = []): Rule;

	public function findOne(array $filter = []): ?Rule;

	/** @return Rule[] */
	public function findAll(): array;

	public function findRelated(Role $role): RuleCollection;

	public function save(Rule $resource): bool;
}
