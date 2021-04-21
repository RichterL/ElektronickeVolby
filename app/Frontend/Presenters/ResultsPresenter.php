<?php

namespace App\Frontend\Presenters;

use App\Frontend\Classes\ElectionsFacade;
use App\Models\Entities\Election\Election;
use App\Models\Mappers\Exception\EntityNotFoundException;

class ResultsPresenter extends BasePresenter
{
	private ElectionsFacade $electionsFacade;
	private Election $election;

	public function __construct(ElectionsFacade $electionsFacade)
	{
		parent::__construct();
		$this->electionsFacade = $electionsFacade;
	}

	public function actionDefault(int $id): void
	{
		try {
			$this->election = $this->electionsFacade->getElectionById($id);
		} catch (EntityNotFoundException $e) {
			$this->error('Election not found');
		}
	}

	public function renderDefault(): void
	{
		$this->template->election = $this->election;
	}
}
