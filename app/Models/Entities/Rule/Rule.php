<?php

namespace App\Models\Entities\Rule;

use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use App\Models\Entities\Resource\Privilege;
use App\Models\Entities\Resource\Resource;
use App\Models\Entities\Role\Role;
use App\Models\Traits\Entity\HasId;

/**
 * @property int $id
 * @property Role $role
 * @property Resource $resource
 * @property Privilege $privilege
 * @property Type $type
 */

class Rule extends Entity implements IdentifiedById
{
	protected Role $role;
	protected Resource $resource;
	protected Privilege $privilege;
	protected Type $type;

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

	public function getRoleId(): ?int
	{
		return $this->role->getId();
	}

	public function getResourceId(): ?int
	{
		return $this->resource->getId();
	}

	public function getPrivilegeId(): ?int
	{
		return $this->privilege->getId();
	}

	public function setRole(Role $role): self
	{
		$this->role = $role;
		return $this;
	}

	public function setType(Type $type)
	{
		$this->type = $type;
	}

	public function setResource(Resource $resource): self
	{
		$this->resource = $resource;
		return $this;
	}

	public function setPrivilege(Privilege $privilege): self
	{
		$this->privilege = $privilege;
		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'role' => $this->role->getId(),
			'resource' => $this->resource->getId(),
			'privilege' => $this->privilege->getId(),
			'type' => $this->type->getValue(),
		];
	}
}
