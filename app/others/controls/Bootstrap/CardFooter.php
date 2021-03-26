<?php

namespace App\Controls\Bootstrap;

use Nette\Application\UI\Control;

class CardFooter
{
	private $content;
	private $elementPrototype;

	public function setCaption(string $caption): self
	{
		$this->caption = $caption;
		return $this;
	}

	public function render()
	{
		return $this->elementPrototype;
	}
}
