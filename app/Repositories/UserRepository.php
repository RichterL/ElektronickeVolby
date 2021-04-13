<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\User;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\IRoleMapper;
use App\Models\Mappers\IUserMapper;

class UserRepository
{
	private IUserMapper $userMapper;
	private IRoleMapper $roleMapper;

	public function __construct(IUserMapper $userMapper, IRoleMapper $roleMapper)
	{
		$this->userMapper = $userMapper;
		$this->roleMapper = $roleMapper;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findById(int $id, bool $includeRoles = true): User
	{
		$user = $this->userMapper->findOne(['id' => $id]);
		if ($includeRoles) {
			$user->setRoles(...$this->roleMapper->findRelated($user));
		}
		return $user;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findByUsername(string $username): User
	{
		return $this->userMapper->findOne(['username' => $username]);
	}

	public function findAll()
	{
		return $this->userMapper->findAll();
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(User $user): bool
	{
		return $this->userMapper->save($user);
	}

	public function getDataSource()
	{
		return $this->userMapper->getDataSource();
	}

	public function delete(User $user): bool
	{
		return $this->userMapper->delete($user);
	}
}
