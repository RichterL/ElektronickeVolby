<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

use App\Models\Entities\BaseEntityIterator;

final class AnswerIterator extends BaseEntityIterator
{
	public function __construct(AnswerCollection $collection)
	{
		$this->collection = $collection;
	}

	/**
	 * @inheritDoc
	 */
	public function current(): Answer
	{
		return $this->collection->offsetGet($this->position);
	}
}
