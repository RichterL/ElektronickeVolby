<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\User;
use Models\Mappers\IRoleMapper;
use Models\Mappers\IUserMapper;

class UserRepository
{
	private IUserMapper $userMapper;
	private IRoleMapper $roleMapper;

	public function __construct(IUserMapper $userMapper, IRoleMapper $roleMapper)
	{
		$this->userMapper = $userMapper;
		$this->roleMapper = $roleMapper;
	}

	public function findById(int $id, bool $includeRoles = true): User
	{
		$user = $this->userMapper->findOne(['id' => $id]);
		if ($includeRoles) {
			$user->setRoles($this->roleMapper->findRelated($user));
		}
		return $user;
	}

	public function findByUsername(string $username): ?User
	{
		return $this->userMapper->findOne(['username' => $username]);
	}

	public function findAll()
	{
		return $this->userMapper->findAll();
	}

	public function save(User $user): bool
	{
		return $this->userMapper->save($user);
	}

	public function saveData(User $user): bool
	{
		return $this->userMapper->saveData($user);
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
