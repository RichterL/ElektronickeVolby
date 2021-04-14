<?php
declare(strict_types=1);

namespace App\Forms\Voting;

use App\Forms\BaseForm;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\Question;

class VotingForm extends BaseForm
{
	private Election $election;

	public function setElection(Election $election): VotingForm
	{
		$this->election = $election;
		return $this;
	}

	public function createComponentForm()
	{
		$form = $this->initForm();
		$form->getElementPrototype()->novalidate(true);
		$form->addHidden('electionId', $this->election->getId());
		$container = $form->addContainer('questions');
		/** @var Question $question */
		foreach ($this->election->getQuestions() as $question) {
			$tmp = [];
			foreach ($question->getAnswers() as $answer) {
				$tmp[$answer->getId()] = $answer->value;
			}
			$checkboxes = $container->addCheckboxList((string) $question->getId(), $question->question, $tmp);
			if ($question->required) {
				$checkboxes->addRule($form::FILLED, 'Answer is required for this question');
			}
			if ($question->getMin() > 1) {
				$checkboxes->addCondition($form::FILLED, true)
					->addRule($form::MIN_LENGTH, 'Please choose at least %d options', $question->getMin());
			}
			$checkboxes->addRule($form::MAX_LENGTH, 'Only %d option(s) can be selected', $question->getMax());

		}
		$form->addSubmit('submit', 'Encrypt the vote');

		return $form;
	}
}
