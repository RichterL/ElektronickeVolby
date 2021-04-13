<?php

namespace App\Models\Entities\Resource;

class PrivilegeCollection implements \IteratorAggregate, \ArrayAccess
{
	private $items;

	public function __construct(Privilege ...$privileges)
	{
		$this->items = $privileges;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}

	public function offsetSet($index, $value)
	{
		if (!$value instanceof Privilege) {
			throw new \InvalidArgumentException('Only instances of Privilege can be added to PrivilegeCollection');
		}
		if (is_null($index) || empty($value->getId())) {
			$this->items[] = $value;
		} else {
			$this->items[$index] = $value;
		}
	}

	public function offsetGet($index)
	{
		return $this->items[$index];
	}

	public function offsetUnset($index)
	{
		unset($this->items[$index]);
	}

	public function offsetExists($index)
	{
		return array_key_exists($index, $this->items);
	}

	public function add(Privilege $privilege)
	{
		$id = $privilege->getId();
		if (empty($id)) {
			$this->items[] = $privilege;
		} else {
			$this->items[$id] = $privilege;
		}
	}

	public function getIdNamePairs()
	{
		$tmp = [];
		foreach ($this->items as $item) {
			$tmp[$item->id] = $item->name;
		}
		return $tmp;
	}
}
