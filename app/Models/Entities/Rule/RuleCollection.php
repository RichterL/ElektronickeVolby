<?php

namespace Models\Entities\Rule;

class RuleCollection implements \IteratorAggregate, \ArrayAccess
{
	private $items;

	public function __construct(Rule ...$rules)
	{
		$this->items = $rules;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}

	public function offsetSet($index, $value)
	{
		if (!$value instanceof Rule) {
			throw new \InvalidArgumentException('Only instances of Rule can be added to RuleCollection');
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

	public function add(Rule $rule)
	{
		$id = $rule->getId();
		if (empty($id)) {
			$this->items[] = $rule;
		} else {
			$this->items[$id] = $rule;
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
