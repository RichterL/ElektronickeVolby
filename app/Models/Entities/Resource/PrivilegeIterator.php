<?php
declare(strict_types=1);

namespace App\Models\Entities\Resource;

use App\Models\Entities\BaseEntityIterator;

class PrivilegeIterator extends BaseEntityIterator
{
	public function __construct(PrivilegeCollection $collection)
	{
		$this->collection = $collection;
	}

	public function current(): Privilege
	{
		return $this->collection->offsetGet($this->position);
	}
}
