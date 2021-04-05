<?php

namespace App\Forms\Election;

use App\Utils\Form\HasMultiplier;
use App\Utils\Form\TextInput;
use Contributte\FormMultiplier\Buttons\CreateButton;
use Contributte\FormMultiplier\Buttons\RemoveButton;
use Models\Entities\Election\Answer;
use Models\Entities\Election\Question;
use Nette;
use Repositories\ElectionRepository;
use Repositories\QuestionRepository;

class QuestionForm extends \App\Forms\BaseForm
{
	use HasMultiplier;

	private $questionRepository;
	private $electionRepository;

	public function __construct(ElectionRepository $electionRepository, QuestionRepository $questionRepository)
	{
		$this->electionRepository = $electionRepository;
		$this->questionRepository = $questionRepository;
	}

	public function createComponentForm()
	{
		$form = $this->initForm();
		$form->getElementPrototype()->class[] = 'form-custom';
		$form->addHidden('id');
		$form->addText('name', 'Name')
			->setRequired()
			->setPlaceholder('Enter reference name');
		$form->addText('question', 'Question')
			->setRequired()
			->setPlaceholder('Enter the question asked ...');
		$form->addInteger('min', 'Minimum answers')
			->addRule($form::MIN, 'Min value is %d', 1)
			->setRequired()
			->setDefaultValue(1);
		$form->addInteger('max', 'Maximum answers')
			->addRule($form::MAX, 'Max value is %d', 10)
			->setRequired()
			->setDefaultValue(1);
		$form->addCheckbox('required', 'Is answer required?')
			->setDefaultValue(true);

		$multiplier = $form->addMultiplier('multiplier', function (Nette\Forms\Container $container, Nette\Forms\Form $form) {
			$text = new TextInput('Answer');
			$text->setRequired()->setPlaceholder('Text of the answer');
			$container->addComponent($text, 'answer');
			$this->dispatchOnRefresh();
		}, $this->copies, $this->maxCopies);

		/** @var CreateButton */
		$createButton = $multiplier->addCreateButton('+')
			->addClass('btn btn-success');
		$createButton->setNoValidate();
		/** @var RemoveButton */
		$removeButton = $multiplier->addRemoveButton('-')
			->addClass('btn btn-danger');

		$form->onSuccess['save'] = [$this, 'processForm'];
		$form->addSubmit('submit', 'Submit');
		$form->addSubmit('cancel', 'Cancel')->setBtnClass('btn-danger')->setOmitted()->setValidationScope([]);
		return $form;
	}

	public function processForm(Nette\Forms\Form $form, array $values)
	{
		if ($form->isSubmitted()->getValue() == 'Cancel') {
			$this->dispatchOnCancel();
			return false;
		}

		$election = $this->electionRepository->findById((int) $this->getPresenter()->getParameter('id'));

		$questionId = (int) $values['id'];
		unset($values['id']);
		$question = ($questionId) ? $this->questionRepository->findById($questionId) : new Question();
		$answers = [];
		foreach ($values['multiplier'] as $value) {
			$tmp = new Answer();
			$tmp->setValue($value['answer']);
			$answers[] = $tmp;
		}
		$question->setValues($values);
		$question->setElection($election);
		$question->setAnswers($answers);
		if (!$this->questionRepository->save($question)) {
			$form->addError('Saving failed');
			return false;
		}
		return true;
	}
}
