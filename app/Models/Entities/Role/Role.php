<?php

declare(strict_types=1);

namespace Models\Entities\Role;

use Models\Entities\Entity;
use Models\Entities\Rule\RuleCollection;

/**
 * @property int|null $id
 * @property string $name
 * @property string $key
 * @property Role|null $parent
 * @property RuleCollection|null $rules
 */
class Role extends Entity
{
	protected ?int $id = null;
	protected string $name;
	protected string $key;
	protected ?Role $parent;
	protected ?RuleCollection $rules = null;

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
			'parent' => $this->parent->getId(),
		];
	}
}
