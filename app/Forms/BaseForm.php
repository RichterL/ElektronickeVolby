<?php

namespace App\Forms;

use Closure;
use Contributte\FormsBootstrap\BootstrapUtils;
use Nette;
use Nette\Utils\Html;
use ReflectionClass;
use App\Utils\Form\BootstrapRenderer;
use App\Utils\Form\BootstrapForm;

abstract class BaseForm extends Nette\Application\UI\Control
{
	private FormFactory $formFactory;
	private BootstrapForm $form;

	public ?Closure $onSuccess = null;
	public ?Closure $onAdd = null;
	public ?Closure $onEdit = null;
	public ?Closure $onRefresh = null;
	public ?Closure $onCancel = null;
	public ?Closure $onSubmit = null;

	public ?Closure $onBeforeSave = null;
	public ?Closure $onSave = null;
	public ?Closure $onError = null;

	protected function initForm(): BootstrapForm
	{
		if (empty($this->form)) {
			$form = $this->formFactory->getForm();
			$form->onError[] = $this->onError ?? static function () {};
			$form->onSubmit[] = $this->onSubmit ?? static function () {};
			$form->onSuccess['beforeSave'] = $this->onBeforeSave ?? static function () {};
			$form->onSuccess['save'] = $this->onSave ?? static function () {};
			$form->onSuccess['afterSave'] = function (Nette\Forms\Form $form, array $values) {
				$callback = (empty($values['id']) ? $this->onAdd : $this->onEdit);
				if ($callback !== null) {
					$callback();
				}
				if ($this->onSuccess !== null) {
					call_user_func($this->onSuccess, $form, $values);
				}
			};
			$this->form = $form;
		}
		return $this->form;
	}

	public function getForm(): BootstrapForm
	{
		return $this->getComponent('form');
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

	public function setValues(array $values): self
	{
		$this->getForm()->setDefaults($values);
		return $this;
	}

	protected function dispatchOnRefresh()
	{
		if ($this->onRefresh !== null) {
			call_user_func($this->onRefresh);
		}
	}

	protected function dispatchOnCancel()
	{
		if ($this->onCancel !== null) {
			call_user_func($this->onCancel);
		}
	}
}
