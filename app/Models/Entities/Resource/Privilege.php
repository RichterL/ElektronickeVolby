<?php

namespace Models\Entities\Resource;

use Models\Entities\Entity;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 */

class Privilege extends Entity
{
	protected ?int $id = null;
	protected string $key;
	protected string $name;

	public function __construct(string $name = null, string $key = null)
	{
		if ($name) {
			$this->name = $name;
		}
		if ($key) {
			$this->key = $key;
		}
	}

	public function setId(int $id): self
	{
		$this->id = $id;
		return $this;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function toArray(): array
	{
		return [
			'name' => $this->name,
			'key' => $this->key,
		];
	}
}
