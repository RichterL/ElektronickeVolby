<?php

namespace App\Utils\Form;

trait HasMultiplier
{
	private int $copies = 1;
	private int $maxCopies = 5;

	public function setMultiplierValues(array $multiplierValues)
	{
		/** @var \Contributte\FormMultiplier\Multiplier */
		$multiplier = $this->getForm()->getComponent('multiplier');
		$multiplier->setValues($multiplierValues);
	}

	public function setMultiplierCopies(int $copies): self
	{
		$this->copies = $copies;
		return $this;
	}

	public function setMultiplierMaxCopies(int $maxCopies): self
	{
		$this->maxCopies = $maxCopies;
		return $this;
	}
}
