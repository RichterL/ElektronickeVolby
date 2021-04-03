<?php
declare(strict_types=1);

namespace Models\Entities\Election;

use Models\Entities\Entity;
use Models\Entities\IdentifiedById;
use Models\Traits\Entity\HasId;

/**
 * @property int|null	$id
 * @property string		$value
 * @property Question	$question
 */
class Answer extends Entity implements IdentifiedById
{
	protected string $value;
	protected Question $question;

	use HasId;

	public function setQuestion(Question $question): self
	{
		$this->question = $question;
		return $this;
	}

	public function setValue(string $value): self
	{
		$this->value = $value;
		return $this;
	}
}
