<?php

namespace App\Models\Entities\Election;

use App\Models\Entities\BaseCollection;

class AnswerCollection extends BaseCollection
{
	protected const CONTAINS = Answer::class;
	/** @var Answer[] $items */
	protected array $items;

	public function __construct(Answer ...$answers)
	{
		$this->items = $answers;
	}

	public function getIterator(): AnswerIterator
	{
		return new AnswerIterator($this);
	}

	public function offsetGet($offset): Answer
	{
		return $this->items[$offset];
	}

	public function add(Answer $answer): void
	{
		$this->items[] = $answer;
	}

	/**
	 * @return string[]
	 */
	public function getIdValuePairs(): array
	{
		$tmp = [];
		foreach ($this->items as $item) {
			$tmp[$item->id] = $item->value;
		}
		return $tmp;
	}

}
