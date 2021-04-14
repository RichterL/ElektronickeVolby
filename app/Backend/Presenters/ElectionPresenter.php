<?php
declare(strict_types=1);

namespace App\Backend\Presenters;

use App\Forms\Election\QuestionForm;
use App\Forms\Election\QuestionFormFactory;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\Question;
use App\Models\Entities\Election\VoterFile;
use App\Core\Classes\CsvResponse;
use Nette\Application\UI\Form;
use App\Repositories\AnswerRepository;
use App\Repositories\ElectionRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\UserRepository;
use App\Repositories\VoterFileRepository;
use App\Repositories\VoterRepository;
use Nette\Http\FileUpload;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataSource\ArrayDataSource;
use App\Backend\Utils\DataGrid\Action;
use App\Backend\Utils\DataGrid\Column;
use App\Backend\Utils\DataGrid\ToolbarButton;

final class ElectionPresenter extends DefaultPresenter
{
	/** @persistent */
	public int $id;
	private ?Election $election = null;

	private ElectionRepository $electionRepository;
	private UserRepository $userRepository;
	private VoterFileRepository $voterFileRepository;
	private VoterRepository $voterRepository;
	private QuestionRepository $questionRepository;
	private AnswerRepository $answerRepository;

	public function __construct(
		ElectionRepository $electionRepository,
		UserRepository $userRepository,
		VoterFileRepository $voterFileRepository,
		VoterRepository $voterRepository,
		QuestionRepository $questionRepository,
		AnswerRepository $answerRepository
	) {
		parent::__construct();
		$this->electionRepository = $electionRepository;
		$this->userRepository = $userRepository;
		$this->voterFileRepository = $voterFileRepository;
		$this->voterRepository = $voterRepository;
		$this->questionRepository = $questionRepository;
		$this->answerRepository = $answerRepository;
	}

	public function startup(): void
	{
		parent::startup();
		try {
			$election = $this->electionRepository->findById((int)$this->getParameter('id'));
			$this->election = $election;
		} catch (EntityNotFoundException $e) {
			$this->error('Election not found!');
		}
	}

	public function beforeRender(): void
	{
		parent::beforeRender();
		$this->template->setFile(__DIR__ . '/templates/Election/default.latte');
		$this->template->election = $this->election;
		$this->redrawControl('formSnippet');
	}

	public function afterRender(): void
	{
		parent::afterRender();
		if ($this->isAjax()) {
			$this->redrawControl('cardSnippet');
		}
	}

	public function renderOverview(): void
	{
		$this->template->selectedTab = 'overview';
	}

	public function renderQuestions(): void
	{
		$this->template->selectedTab = 'questions';
		$questions = $this->questionRepository->findRelated($this->election);
		$this->template->questions = $questions;
	}

	public function renderAnswers(): void
	{
		$this->template->selectedTab = 'answers';
	}

	public function renderVoterFiles(): void
	{
		$this->template->voterFiles = $this->voterFileRepository->findRelated($this->election);
		$this->template->selectedTab = 'voterFiles';
	}

	public function renderVoterList(): void
	{
		$this->template->selectedTab = 'voterList';
	}

	public function handleDeleteVoterFile(int $voterFileId): void
	{
		$voterFile = $this->voterFileRepository->findById($voterFileId);
		if (!$voterFileId) {
			$this->flashMessage('Election wasn\'t found!', 'error');
			return;
		}
		if ($this->voterFileRepository->delete($voterFile)) {
			$this->flashMessage('Voter file ' . $voterFile->filename . ' deleted', 'success');
			$this->getGrid('voterFilesGrid')->reload();
		}
	}

	public function handleShowVoterFileDetail(int $voterFileId): void
	{
		$this->template->voterFileDetail = $this->voterFileRepository->findById($voterFileId);
		$this->template->showVoterFileDetail = true;
		$this->template->showModal = true;
		$this->payload->showModal = true;
		$this->payload->modalId = 'myModal';
		$this->template->modalControl = 'voterFileDetailGrid';
		if ($this->isAjax()) {
			$this->redrawControl('modal');
		}
	}

	public function handleImportVoterList(): void
	{
		$this->template->showImportVoterListForm = true;
		$this->template->showModal = true;
		$this->payload->showModal = true;
		$this->payload->modalId = 'myModal';
		$this->template->modalControl = 'importVoterListForm';
		if ($this->isAjax()) {
			// $this->redrawControl('cardSnippet');
			$this->redrawControl('modal');
			$this->redrawControl('scripts');
		}
	}

	public function handleDownloadVoterFile(int $voterFileId): void
	{
		if ($this->isAjax()) {
			$this->redirectUrl($this->link('downloadVoterFile!', ['voterFileId' => $voterFileId]), 302);
		}
		$voterFile = $this->voterFileRepository->findById($voterFileId);
		$content = $voterFile->getContent();
		$this->sendResponse(new CsvResponse($voterFile->filename, $voterFile->content));
	}

	public function handleApplyVoterFile(int $voterFileId): void
	{
		$voterFile = $this->voterFileRepository->findById($voterFileId);
		$this->voterRepository->importFromFile($this->election, $voterFile);
		$this->flashMessage('Voter file applied!', 'success');
	}

	public function handleEditQuestion(int $questionId): void
	{
		$question = $this->questionRepository->findById($questionId);
		if (!$question) {
			$this->error('Question not found!');
		}
		/** @var QuestionForm */
		$form = $this->getComponent('questionForm');
		$multiplierValues = [];
		foreach ($question->getAnswers() as $answer) {
			$multiplierValues[] = ['answer' => $answer->value];
		}
		$form->setMultiplierCopies(count($multiplierValues));
		$form->setMultiplierValues($multiplierValues);
		$form->setValues($question->toArray());
		$this->template->quesitonEdit = true;
		$this->template->showQuestionForm = true;
		if ($this->isAjax()) {
			$this->redrawControl('formSnippet');
		}
	}

	public function handleShowQuestionForm(): void
	{
		$this->template->showQuestionForm = true;
		if ($this->isAjax()) {
			$this->redrawControl('formSnippet');
		}
	}

	public function handleHideQuestionForm(): void
	{
		$this->template->showQuestionForm = false;
		if ($this->isAjax()) {
			$this->redrawControl('formSnippet');
		}
	}

	public function handleDeleteQuestion(int $questionId): void
	{
		$question = $this->questionRepository->findById($questionId);
		if (!$question) {
			$this->error('Question not found!');
		}

		if ($this->questionRepository->delete($question)) {
			$this->flashMessage('Question deleted!', 'success');
		}
	}

	public function handleDeleteAnswer(int $answerId): void
	{
		try {
			$answer = $this->answerRepository->findById($answerId);
			if (!$this->answerRepository->delete($answer)) {
				$this->flashMessage('delete failed', 'error');
				return;
			}
			$this->flashMessage('answer deleted', 'success');
			if ($this->isAjax()) {
				$this->getGrid('answersGrid')->reload();
				$this->redrawControl('cardSnippet');
			}
		} catch (EntityNotFoundException $e) {
			$this->error('Answer not found!');
		}
	}

	public function handleActivate(): void
	{
		try {
			if ($this->election->active) {
				$this->flashMessage('Election is already active', 'info');
				return;
			}
			$this->election->setActive();
			$this->electionRepository->save($this->election);
			$this->flashMessage('Election activated!', 'success');
		} catch (SavingErrorException $e) {
			$this->flashMessage('Activating failed!', 'error');
		}
	}

	public function handleDeactivate(): void
	{
		try {
			if (!$this->election->active) {
				$this->flashMessage('Election is already inactive', 'info');
				return;
			}
			$this->election->setActive(false);
			$this->electionRepository->save($this->election);
			$this->flashMessage('Election deactivated!', 'success');
		} catch (SavingErrorException $e) {
			$this->flashMessage('Deactivating failed!', 'error');
		}
	}

	public function createComponentVoterFilesGrid(): void
	{
		$this->addGrid('voterFilesGrid', $this->voterFileRepository->getDataSource(['election_id' => $this->getParameter('id')]))
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::FILTERTEXT, 'filename', 'Filename')
			->addColumn(Column::DATETIME, 'created_at', 'Created at')
			->addColumn(Column::TEXT, 'created_by', 'Created by')
			->addAction(Action::VIEW, 'showVoterFileDetail!', ['voterFileId' => 'id'])
			->addAction(Action::APPLY, 'applyVoterFile!', ['voterFileId' => 'id'])
			->addAction(Action::DOWNLOAD, 'downloadVoterFile!', ['voterFileId' => 'id'], false)
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete voter file %s?', 'filename'), 'deleteVoterFile!', ['voterFileId' => 'id']);
	}

	public function createComponentVoterFileDetailGrid(): void
	{
		$voterFile = $this->voterFileRepository->findById((int) $this->getParameter('voterFileId'));
		$content = $voterFile->getContent();
		$fp = fopen('php://temp', 'r+');
		fputs($fp, $content);
		rewind($fp);
		$csv = [];
		while ($line = fgetcsv($fp)) {
			$csv[] = $line;
		}
		$datasource = new ArrayDataSource($csv);
		$this->addGrid('voterFileDetailGrid', $datasource, '2')
			->addColumn(Column::TEXT, '0', 'name')
			->addColumn(Column::TEXT, '1', 'surname')
			->addColumn(Column::TEXT, '2', 'email');
		$this->template->showVoterFileDetail = true;
		$this->template->selectedTab = 'voterList';
		if ($this->isAjax()) {
			$this->redrawControl('cardSnippet');
		}
	}

	public function createComponentVoterListGrid(): void
	{
		$this->addGrid('voterListGrid', $this->voterRepository->getDataSource(['election_id' => $this->election->getId()]))
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::FILTERTEXT, 'email', 'Email')
			->addColumn(Column::BOOL, 'voted', 'voted')
			->addColumn(Column::DATETIME, 'timestamp', 'timestamp');
	}

	public function createComponentQuestionsGrid(): void
	{
		$grid = $this->addGrid('questionsGrid', $this->questionRepository->getDataSource(['election_id' => $this->election->getId()]))
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::FILTERTEXT, 'name', 'Name')
			->addColumn(Column::FILTERTEXT, 'question', 'Question')
			->addColumn(Column::BOOL, 'required', 'Required')
			->addColumn(Column::NUMBER, 'min', 'min')
			->addColumn(Column::NUMBER, 'max', 'max')
			->addAction(Action::EDIT, 'editQuestion!', ['questionId' => 'id'])
			->addAction(Action::DELETE, 'deleteQuestion!', ['questionId' => 'id'])
			->addToolbarButton(ToolbarButton::ADD, 'Add new question', 'showQuestionForm!')
			->getOriginal();
		$grid->setItemsDetail();
		$grid->addInlineAdd()
			->onControlAdd[] = function (\Nette\Forms\Container $container) {
				$container->addText('name', '');
				$container->addText('question', '');
				$container->addSelect('required', '', ['no', 'yes']);
				$container->addSelect('multiple', '', ['no', 'yes']);
			};
		$grid->getInlineAdd()->onSubmit[] = function (\Nette\Utils\ArrayHash $values): void {
			$question = new Question();
			$question->setRequired((bool) $values['required'])
				->setMultiple((bool) $values['multiple'])
				->setName($values['name'])
				->setQuestion($values['question'])
				->setElection($this->election);
			if ($this->questionRepository->save($question)) {
				$this->flashMessage('Question saved');
			}
		};
	}

	public function createComponentAnswersGrid(): void
	{
		$this->addGrid('answersGrid', $this->answerRepository->getDataSource(['election_id' => $this->election->getId()]))
			->addColumn(Column::TEXT, 'question_id', 'question id')
			->addColumn(Column::TEXT, 'question', 'question text')
			->addColumn(Column::TEXT, 'value', 'answer')
			->addAction(Action::DELETE, 'deleteAnswer!', ['answerId' => 'id']);
	}

	public function createComponentImportVoterListForm(): BootstrapForm
	{
		$form = new BootstrapForm();
		$form->setRenderMode(RenderMode::SIDE_BY_SIDE_MODE);
		$form->setAjax();
		$form->addUpload('file', 'Upload')->setHtmlAttribute('accept', '.csv');
		$form->addSubmit('submit', 'Import')->setHtmlAttribute('data-dismiss', 'modal');
		$form->onSuccess[] = [$this, 'importVoterListFormSuccess'];
		return $form;
	}

	public function importVoterListFormSuccess(Form $form, array $values): void
	{
		try {
			/** @var FileUpload */
			$file = $values['file'];
			unset($values['file']);

			$values['filename'] = $file->getSanitizedName();
			$values['content'] = $file->getContents();
			$values['createdAt'] = new \DateTime();
			$values['createdBy'] = $this->userRepository->findById($this->getLoggedUserId(), false);
			$voterFile = new VoterFile();
			$voterFile->setElection($this->electionRepository->findById((int) $this->getParameter('id')));
			$voterFile->setValues($values);
			$this->voterFileRepository->save($voterFile);
			$this->flashMessage('import success', 'success');
			$this->redirect(':voterFiles', ['id' => (int) $this->getParameter('id')]);
		} catch (SavingErrorException | EntityNotFoundException $e) {
			$this->flashMessage('import failed', 'error');
		}
	}

	/** @var QuestionFormFactory @inject */
	public QuestionFormFactory $questionFormFactory;

	public function createComponentQuestionForm(): QuestionForm
	{
		$form = $this->questionFormFactory->create();
		$form->onBeforeSave = function (\Nette\Forms\Form $form, array $values) {
			// check any conditions before saving the form
			// stop saving process by $form->addError()
		};
		$form->onError = function () {
			$this->flashMessage('There were errors in the form', 'warning');
			$this->handleShowQuestionForm();
		};
		$form->onEdit = function () {
			$this->flashMessage('Question saved');
		};
		$form->onAdd = function () {
			$this->flashMessage('Question added');
		};
		$form->onSuccess = function () {
			$this->redirect('this');
		};
		$form->onRefresh = function () {
			$this->template->showQuestionForm = true;
		};
		return $form;
	}
}
