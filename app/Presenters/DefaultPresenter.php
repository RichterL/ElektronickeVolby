<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Controls\Menu;
use Nette;

abstract class DefaultPresenter extends Nette\Application\UI\Presenter
{
	public function createComponentMenu()
	{
		return new Menu();
	}
}
