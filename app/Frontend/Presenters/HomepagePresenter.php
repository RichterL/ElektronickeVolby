<?php
declare(strict_types=1);

namespace App\Frontend\Presenters;

use App\Frontend\Classes\ElectionsFacade;
use Nette;
use Repositories\ElectionRepository;

final class HomepagePresenter extends BasePresenter
{
	private ElectionsFacade $electionsFacade;

	public function __construct(ElectionsFacade $electionsFacade)
	{
		parent::__construct();
		$this->electionsFacade = $electionsFacade;
	}

	public function renderDefault(): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		$this->template->allElections = $this->electionsFacade->getAllElections();
		$this->template->activeElections = $this->electionsFacade->getAllActiveElections();
		$this->template->availableElections = $this->electionsFacade->findVoterInVoterLists($this->getUserEntity());
	}
}
