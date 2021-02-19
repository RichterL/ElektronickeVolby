<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	private Nette\Database\Explorer $database;


	public function __construct(Nette\Database\Explorer $database) {
		$this->database = $database;
	}

	public function renderDefault(): void
	{

		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		$this->getUser()->isAllowed('users', 'view');
		// dump($this->getUser());
	}
}
