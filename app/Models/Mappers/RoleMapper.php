<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Role\Role;
use App\Models\Entities\User;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface RoleMapper
{
	public function create(array $data = []): Role;

	/** @return Role[] */
	public function findRelated(User $user): array;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Role $role): bool;

	/**
	 * @throws DeletingErrorException
	 */
	public function delete(Role $role): bool;

	public function getDataSource(array $filter = []): IDataSource;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Role;

	/** @return Role[] */
	public function find(array $filter = []): iterable;

	/** @return Role[] */
	public function findAll(): array;
}
