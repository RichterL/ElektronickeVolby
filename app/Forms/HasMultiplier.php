<?php
declare(strict_types=1);

namespace App\Forms;

use Contributte\FormMultiplier\Multiplier;

trait HasMultiplier
{
	private int $copies = 1;
	private int $maxCopies = 50;

	public function setMultiplierValues(array $multiplierValues): void
	{
		/** @var Multiplier */
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
