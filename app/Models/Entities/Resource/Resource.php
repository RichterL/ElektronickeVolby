<?php

declare(strict_types=1);

namespace App\Models\Entities\Resource;

use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use App\Models\Traits\Entity\HasId;

/**
 * @property int|null $id
 * @property string $name
 * @property string $key
 * @property PrivilegeCollection $privileges
 * @property Resource|null $parent
 */

class Resource extends Entity implements IdentifiedById
{
	protected ?int $id = null;
	protected string $name;
	protected string $key;
	protected PrivilegeCollection $privileges;
	protected ?Resource $parent = null;

	use HasId;

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
			'parent' => $this->parent ? $this->parent->getId() : null,
		];
	}
}
