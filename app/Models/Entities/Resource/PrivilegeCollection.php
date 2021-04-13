<?php
declare(strict_types=1);

namespace App\Models\Entities\Resource;
use ArrayAccess;
use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * @implements \IteratorAggregate<int, Privilege>
 * @implements \ArrayAccess<int, Privilege>
 */
class PrivilegeCollection implements IteratorAggregate, ArrayAccess
{
	/**
	 * @var Privilege[]
	 */
	private array $items;

	public function __construct(Privilege ...$privileges)
	{
		$this->items = $privileges;
	}

	/**
	 * @return ArrayIterator<int, Privilege>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function offsetSet($offset, $value): void
	{
		if (!$value instanceof Privilege) {
			throw new InvalidArgumentException('Only instances of Privilege can be added to PrivilegeCollection');
		}
		if (is_null($offset) || $value->getId() === null) {
			$this->items[] = $value;
		} else {
			$this->items[$offset] = $value;
		}
	}

	public function offsetGet($offset): Privilege
	{
		return $this->items[$offset];
	}

	public function offsetUnset($offset): void
	{
		unset($this->items[$offset]);
	}

	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->items);
	}

	public function add(Privilege $privilege): void
	{
		$id = $privilege->getId();
		if ($id === null) {
			$this->items[] = $privilege;
		} else {
			$this->items[$id] = $privilege;
		}
	}

	/**
	 * @return string[]
	 */
	public function getIdNamePairs(): array
	{
		$tmp = [];
		foreach ($this->items as $item) {
			$tmp[$item->id] = $item->name;
		}
		return $tmp;
	}
}
