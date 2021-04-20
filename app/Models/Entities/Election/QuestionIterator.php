<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

use App\Models\Entities\BaseEntityIterator;

class QuestionIterator extends BaseEntityIterator
{
	public function __construct(QuestionCollection $collection)
	{
		$this->collection = $collection;
	}

	public function current(): Question
	{
		return $this->collection->offsetGet($this->position);
	}
}
