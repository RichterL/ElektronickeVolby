<?php
declare(strict_types=1);

namespace Models\Entities;

use Models\Entities\Role\Role;

class User extends Entity
{
	protected int $id = 0;
	protected string $username;
	protected string $password;
	protected string $name;
	protected string $surname;
	protected string $email;
	/** @var Role[] */
	protected array $roles = [];

	public function __construct()
	{
		$this->email = '';
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): self
	{
		$this->id = $id;
		return $this;
	}

	public function getUsername(): string
	{
		return $this->username;
	}

	public function setUsername(string $username): self
	{
		$this->username = $username;

		return $this;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	public function setPassword(?string $password): self
	{
		if ($password) {
			$this->password = $password;
		}
		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	// public function getSurname(): string
	// {
	// 	return $this->surname;
	// }

	// public function setSurname(string $surname): self
	// {
	// 	$this->surname = $surname;
	// 	return $this;
	// }

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): self
	{
		$this->email = $email;
		return $this;
	}

	/** @return Role[] */
	public function getRoles(): array
	{
		return $this->roles;
	}

	public function setRoles(array $roles): self
	{
		$this->roles = $roles;
		return $this;
	}

	public function getRolesNames(): array
	{
		$tmp = [];
		foreach ($this->getRoles() as $role) {
			$tmp[] = $role->key;
		}
		return $tmp;
	}

	public function getRolesIds(): array
	{
		$tmp = [];
		/** @var Role */
		foreach ($this->getRoles() as $role) {
			$tmp[] = $role->id;
		}
		return $tmp;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'username' => $this->getUsername(),
			'name' => $this->getName(),
			'surname' => $this->surname,
			'email' => $this->getEmail(),
			'roles' => $this->getRolesIds(),
		];
	}
}
