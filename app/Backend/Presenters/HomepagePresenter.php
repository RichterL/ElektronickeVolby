<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

final class HomepagePresenter extends BasePresenter
{
	public function renderDefault(): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}
}
