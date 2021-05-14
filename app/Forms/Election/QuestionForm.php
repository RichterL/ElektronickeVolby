<?php
declare(strict_types=1);

namespace App\Forms\Election;

use App\Core\Utils\Form\TextInput;
use App\Forms\HasMultiplier;
use App\Models\Entities\Election\AnswerCollection;
use Contributte\FormMultiplier\Buttons\CreateButton;
use Contributte\FormMultiplier\Buttons\RemoveButton;
use App\Models\Entities\Election\Answer;
use App\Models\Entities\Election\Question;
use Nette;
use App\Repositories\ElectionRepository;
use App\Repositories\QuestionRepository;

class QuestionForm extends \App\Forms\BaseForm
{
	use HasMultiplier;

	private QuestionRepository $questionRepository;
	private ElectionRepository $electionRepository;

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
			->addRule($form::MIN, 'Min value is %d', $this->copies)
			->addRule($form::MAX, 'Max value is %d', $this->maxCopies)
			->setRequired()
			->setDefaultValue($this->copies);
		$form->addInteger('max', 'Maximum answers')
			->addRule($form::MAX, 'Max value is %d', $this->maxCopies)
			->addRule($form::MIN, 'Min value is %d', $this->copies)
			->setRequired()
			->setDefaultValue($this->copies);
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
		$form->onSuccess['beforeSave'] = [$this, 'validate'];
		$form->onSuccess['save'] = [$this, 'processForm'];
		$form->addSubmit('submit', 'Submit');
		$form->addSubmit('cancel', 'Cancel')->setBtnClass('btn-danger')->setOmitted()->setValidationScope([]);
		return $form;
	}

	public function validate(Nette\Forms\Form $form, array $values): void
	{
		if ($form->isSubmitted()->getValue() == 'Cancel') {
			return;
		}
		$valid = true;
		if ($values['min'] < $this->copies || $values['min'] > $values['max']) {
			$form['min']->addError('incorrect value');
			$valid = false;
		}
		if ($values['max'] > $this->maxCopies || $values['max'] < $values['min']) {
			$form['max']->addError('incorrect value');
			$valid = false;
		}
		if (!$valid) {
			return;
		}
		if ($values['min'] > count($values['multiplier'])) {
			$form['min']->setValue(count($values['multiplier']));
		}
		if ($values['max'] > count($values['multiplier'])) {
			$form['max']->setValue(count($values['multiplier']));
		}
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
		$answers = new AnswerCollection();
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
