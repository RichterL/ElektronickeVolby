<?php

declare(strict_types=1);

namespace Models\Entities\Role;

use Models\Entities\Entity;

class Role extends Entity
{
	protected int $id;
	protected string $name;
	protected string $key;
	protected Role $parent;

	public function __construct()
	{
	}

	public function getId()
	{
		return $this->id;
	}

	public function setId($id): self
	{
		$this->id = $id;
		return $this;
	}

	public function getParent(): ?Role
	{
		return $this->parent ?? null;
	}

	public function setParent(Role $parent): self
	{
		$this->parent = $parent;
		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'name' => $this->name,
			'key' => $this->key,
			'parent' => $this->getParent(),
		];
	}
}
