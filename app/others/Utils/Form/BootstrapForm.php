<?php

namespace App\Utils\Form;

use Nette;

class BootstrapForm extends \Contributte\FormsBootstrap\BootstrapForm
{
	/**
	 * @param string|Html|null $label
	 * @param int|null $cols ignored
	 * @param int|null $maxLength ignored
	 * @return TextInput
	 */
	public function addText(string $name, $label = null, ?int $cols = null, ?int $maxLength = null): Nette\Forms\Controls\TextInput
	{
		$comp = new TextInput($label);
		$comp->setNullable(self::$allwaysUseNullable);

		if ($cols !== null) {
			$comp->setHtmlAttribute('cols', $cols);
		}

		if ($maxLength !== null) {
			$comp->setHtmlAttribute('maxlength', $cols);
		}

		$this->addComponent($comp, $name);

		return $comp;
	}
}
