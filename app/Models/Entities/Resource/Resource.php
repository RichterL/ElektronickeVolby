<?php

declare(strict_types=1);

namespace Models\Entities\Resource;

use Exception;
use Models\Entities\Entity;

/**
 * @property string $name
 * @property string $key
 * @property Privilege[] $privileges
 */

class Resource extends Entity
{
	protected ?int $id = null;
	protected string $name;
	protected string $key;
	/** @var Privilege[] */
	protected array $privileges;

	public function __construct()
	{
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): self
	{
		$this->id = $id;
		return $this;
	}

	public function addPrivilege(Privilege $privilege, bool $rewrite = false): self
	{
		if ($rewrite === true || !array_key_exists($privilege->getId(), $this->privileges)) {
			$this->privileges[$privilege->getId()] = $privilege;
		} else {
			throw new PrivilegeAlreadyExistsException('Privilege already exists for this resource.');
		}

		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'name' => $this->name,
			'key' => $this->key,
		];
	}
}
