<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

use App\Models\Entities\BaseEntityIterator;
use App\Models\Entities\Resource\Privilege;
use App\Models\Entities\Resource\PrivilegeCollection;

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
