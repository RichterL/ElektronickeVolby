<?php

namespace App\Utils\Form;

use Contributte\FormsBootstrap\Enums\RendererConfig as Cnf;
use Contributte\FormsBootstrap\Enums\RendererOptions;
use Contributte\FormsBootstrap\Inputs\IValidationInput;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class BootstrapRenderer extends \Contributte\FormsBootstrap\BootstrapRenderer
{
	/**
	 * Renders single visual row.
	 */
	public function renderPair(BaseControl $control): string
	{
		$pairHtml = $this->configElem(Cnf::PAIR);

		$pairHtml->id = $control->getOption(RendererOptions::ID);

		$labelHtml = $this->renderLabel($control);

		$nonLabel = $this->getElem(Cnf::NON_LABEL);

		//region non-label parts
		$controlHtml = $this->renderControl($control);
		$feedbackHtml = $this->renderFeedback($control);
		$descriptionHtml = $this->renderDescription($control);

		if (!empty($controlHtml)) {
			$pairHtml->addHtml($controlHtml);
		}

		if (!empty($labelHtml)) {
			$pairHtml->addHtml($labelHtml);
		}

		if (!empty($feedbackHtml)) {
			$nonLabel->addHtml($feedbackHtml);
		}

		if (!empty($descriptionHtml)) {
			$nonLabel->addHtml($descriptionHtml);
		}

		//endregion

		if (!empty($nonLabel)) {
			$pairHtml->addHtml($nonLabel);
		}

		return $pairHtml->render(0);
	}

	public function renderCustomPair(BaseControl $control): string
	{
		$str = Html::el();
		$labelHtml = $this->renderLabel($control);
		$controlHtml = $this->renderControl($control);

		if (!empty($controlHtml)) {
			$str->addHtml($controlHtml);
		}

		if (!empty($labelHtml)) {
			$str->addHtml($labelHtml);
		}

		return $str->render(0);
	}

	public function renderCustomFeedback(?BaseControl $control = null): ?Html
	{
		return $this->renderFeedback($control);
	}

	public function renderCustomDescription(?BaseControl $control = null): ?Html
	{
		return $this->renderDescription($control);
	}

	/**
	 * Renders 'control' part of visual row of controls.
	 */
	public function renderControl(BaseControl $control): string
	{
		/** @var Html $controlHtml */
		$controlHtml = $control->getControl();
		$control->setOption(RendererOptions::_RENDERED, true);
		if (($this->form->showValidation || $control->hasErrors()) && $control instanceof IValidationInput) {
			$controlHtml = $control->showValidation($controlHtml);
		}
		$controlHtml = $this->configElem(Cnf::INPUT, $controlHtml);

		return (string) $controlHtml;
	}
}
