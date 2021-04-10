<?php
declare(strict_types=1);

namespace Models\Entities;

use Models\Entities\Role\Role;
use Nette\Security\IIdentity;

class User extends Entity implements IdentifiedById, IIdentity
{
	protected ?string $username = null;
	protected ?string $password = null;
	protected string $name;
	protected string $surname;
	protected string $email;
	/** @var Role[] */
	protected array $roles = [];

	use \Models\Traits\Entity\HasId;

	public function __construct()
	{
		$this->email = '';
	}

	public function getUsername(): string
	{
		return $this->username ?? $this->email;
	}

	public function setUsername(string $username): self
	{
		$this->username = $username;

		return $this;
	}

	public function getPassword(): ?string
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
	public function getRoles(bool $asEntity = false): array
	{
		return $asEntity ? $this->roles : $this->getRolesNames();
	}

	public function setRoles(Role ...$roles): self
	{
		$this->roles = $roles;
		return $this;
	}

	public function getRolesNames(): array
	{
		$tmp = [];
		foreach ($this->getRoles(true) as $role) {
			$tmp[] = $role->key;
		}
		return $tmp;
	}

	public function getRolesIds(): array
	{
		$tmp = [];
		/** @var Role */
		foreach ($this->getRoles(true) as $role) {
			$tmp[] = $role->id;
		}
		return $tmp;
	}

	public function getData(): array
	{
		return $this->toArray();
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
