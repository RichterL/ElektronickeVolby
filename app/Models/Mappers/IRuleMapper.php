<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Role\Role;
use Models\Entities\Rule\Rule;
use Models\Entities\Rule\RuleCollection;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IRuleMapper
{
	public function create(array $data = []): Rule;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): ?Rule;

	/** @return Rule[] */
	public function findAll(): array;

	public function findRelated(Role $role): RuleCollection;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Rule $rule): bool;

	public function delete(Rule $rule): bool;

	public function getDataSource(): IDataSource;
}
