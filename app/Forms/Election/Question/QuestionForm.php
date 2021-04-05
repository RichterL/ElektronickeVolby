<?php

namespace App\Forms\Election;

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
	private $questionRepository;
	private $electionRepository;

	public function __construct(ElectionRepository $electionRepository, QuestionRepository $questionRepository)
	{
		$this->electionRepository = $electionRepository;
		$this->questionRepository = $questionRepository;
	}

	public function createComponentForm()
	{
		$form = $this->getForm();
		$form->getElementPrototype()->class[] = 'form-custom';
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

		$copies = 1;
		$maxCopies = 5;

		$multiplier = $form->addMultiplier('multiplier', function (Nette\Forms\Container $container, Nette\Forms\Form $form) {
			$text = new TextInput('Answer');
			$text->setRequired()->setPlaceholder('Text of the answer');
			$container->addText('test', 'test');
			$container->addComponent($text, 'answer');
		}, $copies, $maxCopies);

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

	public function processForm(Nette\Forms\Form $form, $values)
	{
		if ($form->isSubmitted()->getValue() == 'Cancel') {
			$this->getPresenter()->flashMessage('form canceled');
			$this->getPresenter()->redirect(':questions');
		}

		$election = $this->electionRepository->findById((int) $this->getPresenter()->getParameter('id'));

		$question = new Question();
		$answers = [];
		foreach ($values['multiplier'] as $value) {
			$tmp = new Answer();
			$tmp->setValue($value->answer);
			$answers[] = $tmp;
		}
		$question->setAnswers($answers);
		$question->setRequired((bool) $values['required'])
			// ->setMultiple((bool) $values['multiple'])
			->setName($values['name'])
			->setQuestion($values['question'])
			->setElection($election);
		if (!$this->questionRepository->save($question)) {
			$form->addError('Saving failed');
		}
	}
}
