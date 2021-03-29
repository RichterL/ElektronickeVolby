<?php
declare(strict_types=1);

namespace Models\Mappers;

use Models\Entities\User;

interface IUserMapper
{
	public function create(array $data = []): User;

	public function saveData(User $user): bool;

	public function save(User $user): bool;

	public function getDataSource();

	public function delete(User $user): bool;

	public function findOne(array $filter = []): ?User;

	/** @return User[] */
	public function findAll(): array;
}
