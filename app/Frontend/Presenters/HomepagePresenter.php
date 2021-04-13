<?php
declare(strict_types=1);

namespace App\Frontend\Presenters;

use App\Frontend\Classes\ElectionsFacade;

final class HomepagePresenter extends BasePresenter
{
	private ElectionsFacade $electionsFacade;

	public function __construct(ElectionsFacade $electionsFacade)
	{
		parent::__construct();
		$this->electionsFacade = $electionsFacade;
	}

	public function actionDefault(): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function renderDefault(): void
	{
		$this->template->allElections = $this->electionsFacade->getAllElections();
		$this->template->activeElections = $this->electionsFacade->getAllActiveElections();
		$this->template->availableElections = $this->electionsFacade->findVoterInVoterLists($this->getUserEntity());
	}
}
