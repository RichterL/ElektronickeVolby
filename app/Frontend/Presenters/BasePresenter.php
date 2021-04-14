<?php

declare(strict_types=1);

namespace App\Frontend\Presenters;

use App\Models\Entities\User;
use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public function checkRequirements($element): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			if ($this->isAjax()) {
				$this->payload->forceRedirect = true;
			}
			$this->redirect('Sign:in');
		}
		parent::checkRequirements($element);
	}

	public function beforeRender(): void
	{
		if ($this->isAjax()) {
			$id = $this->getParameterId('flash');
			if (!empty($this->getPresenter()->getFlashSession()->$id)) {
				$this->redrawControl('flashes');
			}

			if ((bool) $this->getParameter('isModal')) {
				$this->payload->showModal = true;
				$this->payload->modalId = 'myModal';
				$this->redrawControl('modal');
			}
		}
	}

	public function getUserEntity(): User
	{
		$identity = $this->getUser()->getIdentity();
		if ($identity instanceof User) {
			return $identity;
		}
		throw new \Exception('Invalid identity provided');
	}
}
