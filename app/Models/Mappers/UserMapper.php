<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\User;

interface UserMapper
{
	public function create(array $data = []): User;

	/**
	 * @throws SavingErrorException
	 */
	public function save(User $user): bool;

	public function getDataSource(array $filter = []);

	/**
	 * @throws DeletingErrorException
	 */
	public function delete(User $user): bool;

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): User;

	/** @return User[] */
	public function findAll(): array;
}
