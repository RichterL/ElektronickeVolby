<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Controls\Menu;
use Nette;
use Nette\Application\UI\Form;

abstract class DefaultPresenter extends Nette\Application\UI\Presenter
{
	public function createComponentMenu()
	{
		return new Menu();
	}

	protected function getForm(string $name): Form
	{
		$component = $this->getComponent($name);
		if ($component instanceof Form) {
			return $component;
		}
	}
}
