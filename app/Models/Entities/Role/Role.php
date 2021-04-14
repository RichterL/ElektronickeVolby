<?php

declare(strict_types=1);

namespace App\Models\Entities\Role;

use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use App\Models\Entities\Rule\RuleCollection;
use App\Models\Traits\Entity\HasId;

/**
 * @property int|null $id
 * @property string $name
 * @property string $key
 * @property Role|null $parent
 * @property RuleCollection|null $rules
 */
class Role extends Entity implements IdentifiedById
{
	protected ?int $id = null;
	protected string $name;
	protected string $key;
	protected ?Role $parent = null;
	protected ?RuleCollection $rules = null;

	use HasId;

	public function setParent(Role $parent): self
	{
		$this->parent = $parent;
		return $this;
	}

	public function addRules(RuleCollection $ruleCollection)
	{
		$this->rules = $ruleCollection;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'name' => $this->name,
			'key' => $this->key,
			'parent' => $this->parent ? $this->parent->getId() : null,
		];
	}

	public function __toString(): string
	{
		return $this->key;
	}
}
