<?php
declare(strict_types=1);

namespace App\Models\Entities\Rule;

use ArrayAccess;
use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * @implements \IteratorAggregate<int, Rule>
 * @implements \ArrayAccess<int, Rule>
 */
class RuleCollection implements IteratorAggregate, ArrayAccess
{
	/**
	 * @var Rule[]
	 */
	private array $items;

	public function __construct(Rule ...$rules)
	{
		$this->items = $rules;
	}

	/**
	 * @return ArrayIterator<int, Rule>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	public function offsetSet($offset, $value): void
	{
		if (!$value instanceof Rule) {
			throw new InvalidArgumentException('Only instances of Rule can be added to RuleCollection');
		}
		if (is_null($offset) || $value->getId() === null) {
			$this->items[] = $value;
		} else {
			$this->items[$offset] = $value;
		}
	}

	public function offsetGet($offset): Rule
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

	public function add(Rule $rule): void
	{
		$id = $rule->getId();
		if ($id === null) {
			$this->items[] = $rule;
		} else {
			$this->items[$id] = $rule;
		}
	}

	public function getByTypes(): array
	{
		$tmp = [];
		foreach ($this->items as $item) {
			$tmp[$item->type->getName()][$item->resource->key][] = $item->privilege->key;
		}
		return $tmp;
	}
}
