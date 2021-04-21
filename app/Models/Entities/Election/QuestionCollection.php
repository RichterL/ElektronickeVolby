<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

use App\Models\Entities\BaseCollection;

class QuestionCollection extends BaseCollection
{
	/**
	 * @var Question[]
	 */
	protected array $items;
	protected const CONTAINS = Question::class;

	public function __construct(Question ...$questions)
	{
		$this->items = $questions;
	}

	public function getIterator(): QuestionIterator
	{
		return new QuestionIterator($this);
	}

	public function offsetGet($offset): Question
	{
		return $this->items[$offset];
	}
}
