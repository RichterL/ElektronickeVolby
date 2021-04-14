<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use App\Models\Traits\Entity\HasId;

/**
 * @property int|null	$id
 * @property string		$value
 * @property Question	$question
 */
class Answer extends Entity implements IdentifiedById
{
	protected string $value;
	protected ?Question $question = null;

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

	public function getQuestion(): ?Question
	{
		return $this->question;
	}
}
