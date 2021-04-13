<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Role\Role;
use App\Models\Entities\Rule\Rule;
use App\Models\Entities\Rule\RuleCollection;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface RuleMapper
{
	public function create(array $data = []): Rule;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Rule;

	/** @return Rule[] */
	public function findAll(): array;

	public function findRelated(Role $role): RuleCollection;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Rule $rule): bool;

	public function delete(Rule $rule): bool;

	public function getDataSource(array $filter = []): IDataSource;
}
