<?php

namespace Models\Entities\Resource;

use Models\Entities\Entity;
use Models\Entities\IdentifiedById;
use Models\Traits\Entity\HasId;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 */

class Privilege extends Entity implements IdentifiedById
{
	protected ?int $id = null;
	protected string $key;
	protected string $name;

	use HasId;

	public function __construct(string $name = null, string $key = null)
	{
		if ($name) {
			$this->name = $name;
		}
		if ($key) {
			$this->key = $key;
		}
	}

	public function toArray(): array
	{
		return [
			'name' => $this->name,
			'key' => $this->key,
		];
	}
}
