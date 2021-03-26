<?php

namespace App\Controls\Bootstrap;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\Html;

class Card extends Control
{
	/** @var Form */
	private $form;
	private $id;

	public function __construct($name)
	{
		$this->id = $name;
	}

	public function render()
	{
		// $str = '';
		// $el = Html::el('div')->class('card');
		// $body = Html::el('div')->class('card-body collapse show')->id($this->getBodyId());
		// $el->addHtml($this->getHeader());
		// $el->addHtml($body);

		// if ($this->form) {
		// 	$str = $this->form->getRenderer()->render($this->form);
		// 	$body->addHtml($str);
		// }
		// echo $el;
		$this->template->form = $this->form;
		$this->template->formName = $this->id;

		$this->template->setFile(APP_DIR . '/others/controls/templates/card.latte');
		$this->template->render();
	}

	public function getBodyId()
	{
		return $this->id . '-body';
	}

	public function addForm($name): Form
	{
		$form = new BootstrapForm();
		$this->addComponent($form, $name);
		$form->setRenderMode(RenderMode::SIDE_BY_SIDE_MODE);
		// $form = $this->getComponent($name);
		$this->form = $form;
		return $form;
	}

	// public function createComponentCardForm($name)
	// {
	// 	$form = new BootstrapForm();
	// 	$this->addComponent($form, $name);
	// }

	// public function createComponentHeader()
	// {
	// 	$header = new CardHeader();
	// 	$this->addComponent($header, 'header');
	// }

	public function createComponentHeader()
	{
		return $this->header;
	}

	private $header;

	public function addHeader()
	{
		$header = new CardHeader();
		$this->addComponent($header, 'header');
		$this->header = $header;
		// return $this->getComponent('header');
		return $header;
	}

	public function getHeader()
	{
		return $this->getComponent('header');
	}
}
