<?php

namespace App\Controls\Bootstrap;

use Nette\Application\UI\Control;
use Nette\Utils\Html;

class CardHeader extends Control
{
	private $caption;
	private $minimize = false;
	private $element;

	public function __construct()
	{
		$this->element = Html::el('div')->class('card-header')->setHtml('header');
	}

	public function getElement()
	{
		return $this->element;
	}

	public function setCaption(string $caption): self
	{
		$this->caption = $caption;
		return $this;
	}

	public function addMinimize()
	{
		$target = '#' . $this->getParent()->getBodyId();
		$el = Html::el('a')->class('float-right stretched-link')->href('#')->setAttribute('data-toggle', 'collapse')->setAttribute('data-target', $target);
		$icon = Html::el('i')->class('fa fa-chevron-up');
		$el->addHtml($icon);
		$this->element->addHtml($el);
	}

	public function render()
	{
		return (string) $this->element;
		$str = '<div class="card-header">';
		$str .= $this->caption;
		if ($this->minimize) {
			$str .= '<a class="float-right stretched-link" href="#" data-toggle="collapse" data-target="#userForm" aria-expanded="true">
			<i class="fa fa-chevron-up"></i>
			</a>';
		}
		$str .= '</div>';
	}

	public function __toString()
	{
		return $this->render();
	}
}
