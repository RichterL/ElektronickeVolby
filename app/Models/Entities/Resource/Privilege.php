<?php
declare(strict_types=1);

namespace App\Models\Entities\Resource;

use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use App\Models\Traits\Entity\HasId;

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
			'id' => $this->getId(),
			'name' => $this->name,
			'key' => $this->key,
		];
	}
}
