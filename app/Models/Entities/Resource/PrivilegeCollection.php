<?php
declare(strict_types=1);

namespace App\Models\Entities\Resource;

use App\Models\Entities\BaseCollection;

class PrivilegeCollection extends BaseCollection
{
	/**
	 * @var Privilege[]
	 */
	protected array $items;
	protected const CONTAINS = Privilege::class;

	public function __construct(Privilege ...$privileges)
	{
		$this->items = $privileges;
	}

	public function getIterator(): PrivilegeIterator
	{
		return new PrivilegeIterator($this);
	}

	public function offsetGet($offset): Privilege
	{
		return $this->items[$offset];
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
