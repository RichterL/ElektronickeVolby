<?php

declare(strict_types=1);

namespace Repositories;

use Models\Entities\User;
use Models\Mappers\UserMapper;

class UserRepository
{
	private UserMapper $userMapper;

	public function __construct(UserMapper $userMapper)
	{
		$this->userMapper = $userMapper;
	}

	public function findById(int $id): User
	{
		return $this->userMapper->findOne(['id' => $id]);
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
}
