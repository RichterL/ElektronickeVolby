<?php
declare(strict_types=1);

namespace Models\Entities\Election;

use Models\Entities\Entity;
use Models\Entities\IdentifiedById;
use Models\Traits\Entity\HasId;

/**
 * @property int|null	$id
 * @property string		$name
 * @property string		$question
 * @property Election	$election
 * @property Answer[]	$answers
 * @property bool		$required
 * @property bool		$multiple
 */
class Question extends Entity implements IdentifiedById
{
	protected string $name;
	protected string $question;
	protected Election $election;
	protected ?array $answers = null;
	protected bool $required;
	protected bool $multiple;

	use HasId;

	public function setRequired(bool $required = true): self
	{
		$this->required = $required;
		return $this;
	}

	public function setMultiple(bool $multiple = true): self
	{
		$this->multiple = $multiple;
		return $this;
	}

	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	public function setQuestion(string $question): self
	{
		$this->question = $question;
		return $this;
	}

	public function setElection(Election $election): self
	{
		$this->election = $election;
		return $this;
	}

	/** @return Answer[] */
	public function getAnswers(): ?array
	{
		return $this->answers;
	}

	/** @param Answer[] */
	public function setAnswers(array $answers): self
	{
		/** @var Answer */
		foreach ($answers as $answer) {
			$answer->setQuestion($this);
		}
		$this->answers = $answers;
		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'name' => $this->name,
			'question' => $this->question,
			'requried' => $this->required,
		];
	}
}
