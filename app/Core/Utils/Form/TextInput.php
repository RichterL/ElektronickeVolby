<?php
declare(strict_types=1);

namespace App\Core\Utils\Form;

use Contributte\FormsBootstrap\BootstrapUtils;
use Contributte\FormsBootstrap\Inputs\TextInput as InputsTextInput;
use Nette\Utils\Html;

class TextInput extends InputsTextInput
{
	/**
	 * @inheritdoc
	 */
	public function getControl(): Html
	{
		$control = parent::getControl();
		BootstrapUtils::standardizeClass($control);

		$control->class[] = 'form-control';
		$control->setAttribute('placeholder', $this->placeholder ?? ' ');

		if ($this->autocomplete !== null) {
			$control->setAttribute('autocomplete', $this->autocomplete ? 'on' : 'off');
		}

		return $control;
	}
}
