<?php

declare(strict_types=1);

namespace App\Frontend\Presenters;

use Nette;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	public function renderDefault(): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}
}
