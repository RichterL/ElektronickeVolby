<?php
declare(strict_types=1);

namespace App\Models\Entities;

abstract class BaseEntityIterator implements EntityIterator
{
	protected EntityCollection $collection;
	protected int $position = 0;

	/**
	 * @inheritDoc
	 */
	public function next(): void
	{
		$this->position++;
	}

	/**
	 * @inheritDoc
	 */
	public function key(): int
	{
		$current = $this->current();
		if ($current instanceof IdentifiedById) {
			return $current->getId();
		}
		return $this->position;
	}

	/**
	 * @inheritDoc
	 */
	public function valid(): bool
	{
		return $this->collection->offsetExists($this->position);
	}

	/**
	 * @inheritDoc
	 */
	public function rewind(): void
	{
		$this->position = 0;
	}
}
