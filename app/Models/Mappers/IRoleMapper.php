<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\Role\Role;
use Models\Entities\User;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface IRoleMapper
{
	public function create(array $data = []): Role;

	/** @return Role[] */
	public function findRelated(User $user): array;

	public function save(Role $role): bool;

	public function getDataSource(): IDataSource;

	public function findOne(array $filter = []): ?Role;

	/** @return Role[] */
	public function findAll(): array;
}
