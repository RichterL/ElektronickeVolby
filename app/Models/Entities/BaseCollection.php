<?php
declare(strict_types=1);

namespace App\Models\Entities;

use InvalidArgumentException;

abstract class BaseCollection implements EntityCollection
{
	/** @var Entity[] $items  */
	protected array $items;
	protected const CONTAINS = Entity::class;

	public function offsetSet($offset, $value): void
	{
		$c = static::CONTAINS;
		if (!$value instanceof $c) {
			throw new InvalidArgumentException('Only instances of ' . static::CONTAINS . ' can be added to ' . static::class);
		}
		$this->items[] = $value;
	}

	public function offsetUnset($offset): void
	{
		unset($this->items[$offset]);
	}

	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->items);
	}
}
