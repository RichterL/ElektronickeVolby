<?php
declare(strict_types=1);

namespace App\Models\Entities;

/**
 * @extends \ArrayAccess<int, Entity>
 * @extends \IteratorAggregate<int, Entity>
 */
interface EntityCollection extends \ArrayAccess, \IteratorAggregate
{
	public function getIterator(): EntityIterator;

	public function offsetSet($offset, $value): void;

	public function offsetGet($offset): Entity;

	public function offsetUnset($offset): void;

	public function offsetExists($offset): bool;
}
