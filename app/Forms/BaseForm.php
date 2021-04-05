<?php

namespace App\Forms;

use Contributte\FormsBootstrap\BootstrapUtils;
use Nette;
use Nette\Utils\Html;
use ReflectionClass;
use App\Utils\Form\BootstrapRenderer;
use Contributte\FormsBootstrap\BootstrapForm;

abstract class BaseForm extends Nette\Application\UI\Control
{
	private FormFactory $formFactory;

	/** @var BootstrapForm */
	private $form = null;
	public $onBeforeSave = null;
	public $onSave = null;
	public $onAfterSave = null;
	// private $onSubmit;
	// private $onError;

	private function initForm()
	{
		$form = $this->formFactory->getForm();
		$form->onSuccess['beforeSave'] = $this->onBeforeSave ?? function () {};
		$form->onSuccess['save'] = $this->onSave ?? function () {};
		$form->onSuccess['afterSave'] = $this->onAfterSave ?? [$this, 'onAfterSave'];
		$form->onError[] = function () {
			$this->getPresenter()->flashMessage('some error', 'error');
		};
		$this->form = $form;
	}

	public function getForm()
	{
		if (empty($this->form)) {
			$this->initForm();
		}
		return $this->form;
	}

	public function setFormFactory(FormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	public function render()
	{
		if (!$this->template instanceof Nette\Bridges\ApplicationLatte\Template) {
			throw new Nette\NotSupportedException('Only Latte templates are supported by this form');
		}
		/** @var Nette\Bridges\ApplicationLatte\Template */
		$template = $this->template;
		$template->addFilter('formPair', function ($control) {
			/** @var BootstrapRenderer $renderer */
			$renderer = $control->form->renderer;
			$renderer->attachForm($control->form);
			return $renderer->renderPair($control);
		});
		$template->addFilter('formFeedback', function ($control) {
			/** @var BootstrapRenderer $renderer */
			$renderer = $control->form->renderer;
			$renderer->attachForm($control->form);
			return $renderer->renderCustomFeedback($control);
		});
		$template->addFilter('formDescription', function ($control) {
			/** @var BootstrapRenderer $renderer */
			$renderer = $control->form->renderer;
			$renderer->attachForm($control->form);
			return $renderer->renderCustomDescription($control);
		});
		$template->addFilter('inputGroup', function ($control) {
			/** @var BootstrapRenderer $renderer */
			$renderer = $control->form->renderer;
			$renderer->attachForm($control->form);
			return $renderer->renderCustomPair($control);
		});
		$rc = new ReflectionClass(static::class);
		$dir = dirname($rc->getFileName());
		$filename = lcfirst($rc->getShortName()) . '.latte';
		$this->template->render($dir . '/' . $filename);
	}

	public function addClass(Html $control, string $class): Html
	{
		$classes = BootstrapUtils::standardizeClass($control);
		$classes[] = $class;
		$control->class(implode($classes));
		return $control;
	}
}
