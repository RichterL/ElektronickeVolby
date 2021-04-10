<?php
declare(strict_types=1);

namespace App\Frontend\Presenters;

use App\Forms\Voting\VotingForm;
use App\Forms\Voting\VotingFormFactory;
use App\Frontend\Classes\ElectionsFacade;
use Models\Entities\Election\Election;
use Nette\Forms\Form;

class VotingPresenter extends BasePresenter
{
	private ElectionsFacade $electionsFacade;
	private ?Election $election = null;

	public function __construct(ElectionsFacade $electionsFacade)
	{
		parent::__construct();
		$this->electionsFacade = $electionsFacade;
	}

	public function actionDefault(int $id): void
	{
		$this->election = $this->electionsFacade->getElectionById($id);
	}

	public function renderDefault(): void
	{
		$this->template->election = $this->election;
	}

	/** @var VotingFormFactory @inject */
	public VotingFormFactory $votingFormFactory;

	public function createComponentVotingForm(): VotingForm
	{
		$form = $this->votingFormFactory->create();
		$form->setElection($this->election);
		$form->onError = function (Form $form) {
			$this->flashMessage('error');
		};
		$form->onSubmit = function () {
			$this->flashMessage('Form submitted directly without encrypting the vote, aborting.', 'error');
		};

		return $form;
	}
}
