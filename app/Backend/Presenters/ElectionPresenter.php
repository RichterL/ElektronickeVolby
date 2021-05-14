<?php
declare(strict_types=1);

namespace App\Backend\Presenters;

use App\Backend\Classes\VoteCounting\BallotCounter;
use App\Forms\Election\QuestionForm;
use App\Forms\Election\QuestionFormFactory;
use App\Models\Entities\User;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\Question;
use App\Models\Entities\Election\VoterFile;
use App\Core\Classes\CsvResponse;
use Contributte\PdfResponse\PdfResponse;
use Nette\Application\UI\Form;
use App\Repositories\AnswerRepository;
use App\Repositories\ElectionRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\UserRepository;
use App\Repositories\VoterFileRepository;
use App\Repositories\VoterRepository;
use Nette\Http\FileUpload;
use Nette\InvalidStateException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataSource\ArrayDataSource;
use App\Backend\Utils\DataGrid\Action;
use App\Backend\Utils\DataGrid\Column;
use App\Backend\Utils\DataGrid\ToolbarButton;

final class ElectionPresenter extends BasePresenter
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
	private BallotCounter $ballotCounter;

	public function __construct(
		ElectionRepository $electionRepository,
		UserRepository $userRepository,
		VoterFileRepository $voterFileRepository,
		VoterRepository $voterRepository,
		QuestionRepository $questionRepository,
		AnswerRepository $answerRepository,
		BallotCounter $ballotCounter
	) {
		parent::__construct();
		$this->electionRepository = $electionRepository;
		$this->userRepository = $userRepository;
		$this->voterFileRepository = $voterFileRepository;
		$this->voterRepository = $voterRepository;
		$this->questionRepository = $questionRepository;
		$this->answerRepository = $answerRepository;
		$this->ballotCounter = $ballotCounter;
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

	/* OVERVIEW */
	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(view)
	 */
	public function renderOverview(): void
	{
		$this->template->selectedTab = 'overview';
	}

	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(activate)
	 */
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
		} catch (InvalidStateException $e) {
			$this->flashMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(activate)
	 */
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
		} catch (InvalidStateException $e) {
			$this->flashMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(decrypt)
	 */
	public function handleCountBallots(): void
	{
		try {
			$results = $this->ballotCounter->processBallots($this->election);
			$results['countVoted'] = $this->voterRepository->getCountVoted($this->election);
			$results['countTotal'] = $this->voterRepository->getCountTotal($this->election);
			$this->election->setResults($results);
			$this->electionRepository->save($this->election);
			$this->flashMessage('Votes were counted');
			$this->redirect(':results');
		} catch (SavingErrorException $e) {
			$this->flashMessage('Result saving failed, check logs', 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(importKey)
	 */
	public function handleImportPublicKey(): void
	{
		if ($this->election->isRunning()) {
			$this->flashMessage('Cannot change running election!', 'error');
			return;
		}
		$this->template->showImportKeyForm = true;
		$this->template->showModal = true;
		$this->payload->showModal = true;
		$this->template->modalTitle = 'Import public key';
		$this->payload->modalId = 'myModal';
		$this->template->modalControl = 'importPublicKeyForm';
		if ($this->isAjax()) {
			$this->redrawControl('modal');
			$this->redrawControl('scripts');
		}
	}

	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(importKey)
	 */
	public function handleImportPrivateKey(): void
	{
		if (!$this->election->isFinished()) {
			$this->flashMessage('Cannot upload decryption key before election ends!', 'error');
			return;
		}
		$this->template->showImportKeyForm = true;
		$this->template->showModal = true;
		$this->payload->showModal = true;
		$this->template->modalTitle = 'Import decryption key';
		$this->payload->modalId = 'myModal';
		$this->template->modalControl = 'importPrivateKeyForm';
		if ($this->isAjax()) {
			$this->redrawControl('modal');
			$this->redrawControl('scripts');
		}
	}

	public function createComponentImportPublicKeyForm(): BootstrapForm
	{
		$form = $this->createImportKeyForm();
		$form->onSuccess[] = [$this, 'importPublicKeyFormSuccess'];
		return $form;
	}

	public function createComponentImportPrivateKeyForm(): BootstrapForm
	{
		$form = $this->createImportKeyForm();
		$form->onSuccess[] = [$this, 'importPrivateKeyFormSuccess'];
		return $form;
	}

	public function createImportKeyForm(): BootstrapForm
	{
		$form = new BootstrapForm();
		$form->setRenderMode(RenderMode::SIDE_BY_SIDE_MODE);
		$form->setAjax();
		$form->addUpload('file', 'Upload')->setHtmlAttribute('accept', '.pem');
		$form->addSubmit('submit', 'Import')->setHtmlAttribute('data-dismiss', 'modal');
		return $form;
	}

	public function importPublicKeyFormSuccess(Form $form, array $values): void
	{
		try {
			/** @var FileUpload $file */
			$file = $values['file'];
			unset($values['file']);
			$values['content'] = $file->getContents();
			$values['createdAt'] = new \DateTime();
			$values['createdBy'] = $this->getUser()->getIdentity();
			$this->election->setEncryptionKey($file->getContents());
			$this->electionRepository->save($this->election);
			$this->flashMessage('import success', 'success');
			$this->redirect(':overview', ['id' => (int) $this->getParameter('id')]);
		} catch (SavingErrorException $e) {
			$this->flashMessage('import failed', 'error');
		} catch (\RuntimeException | \InvalidArgumentException $e) {
			$this->flashMessage($e->getMessage(), 'error');
		}
	}

	public function importPrivateKeyFormSuccess(Form $form, array $values): void
	{
		try {
			/** @var FileUpload $file */
			$file = $values['file'];
			unset($values['file']);
			$this->election->setDecryptionKey($file->getContents());
			$this->electionRepository->save($this->election);
			$this->flashMessage('import success', 'success');
			$this->redirect(':overview', ['id' => (int) $this->getParameter('id')]);
		} catch (SavingErrorException $e) {
			$this->flashMessage('import failed', 'error');
		} catch (\RuntimeException | \InvalidArgumentException $e) {
			$this->flashMessage($e->getMessage(), 'error');
		}
	}


	/* RESULTS */

	/**
	 * @restricted
	 * @resource(results)
	 * @privilege(view)
	 */
	public function renderResults(): void
	{
		$this->template->selectedTab = 'results';
		if ($this->isAjax()) {
			$this->redrawControl('scripts');
		}
	}

	/**
	 * @restricted
	 * @resource(results)
	 * @privilege(view)
	 */
	public function handleDownloadProtocol(): void
	{
		if (empty($this->election->results)) {
			$this->flashMessage('Votes haven\'t been counted yet!', 'warning');
			return;
		}

		$template = $this->createTemplate();
		$template->setFile(__DIR__ . "/templates/Election/protocol.latte");
		$template->election = $this->election;

		$pdf = new PdfResponse($template);
		$pdf->setSaveMode(PdfResponse::INLINE);
		$now = (new \DateTime())->format('d.m.Y H:i:s');
		/** @var User $user */
		$user = $this->getUser()->getIdentity();
		$pdf->getMPDF()->setFooter("|Printed at $now <br> by {$user->getName()} {$user->getSurname()}|");
		$this->sendResponse($pdf);
	}

	/* QUESTIONS */

	/**
	 * @restricted
	 * @resource(questions)
	 * @privilege(view)
	 */
	public function renderQuestions(): void
	{
		$this->template->selectedTab = 'questions';
		$questions = $this->questionRepository->findRelated($this->election);
		$this->template->questions = $questions;
	}

	/**
	 * @restricted
	 * @resource(questions)
	 * @privilege(edit)
	 */
	public function handleEditQuestion(int $questionId): void
	{
		if ($this->election->isRunning()) {
			$this->flashMessage('Cannot change running election!', 'error');
			return;
		}
		try {
			$question = $this->questionRepository->findById($questionId);
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
		} catch (EntityNotFoundException $e) {
			$this->error('Question not found!');
		}
	}

	/**
	 * @restricted
	 * @resource(questions)
	 * @privilege(edit)
	 */
	public function handleShowQuestionForm(): void
	{
		if ($this->election->isRunning()) {
			$this->flashMessage('Cannot change running election!', 'error');
			return;
		}
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

	/**
	 * @restricted
	 * @resource(questions)
	 * @privilege(delete)
	 */
	public function handleDeleteQuestion(int $questionId): void
	{
		if ($this->election->isRunning()) {
			$this->flashMessage('Cannot change running election!', 'error');
			return;
		}
		try {
			$question = $this->questionRepository->findById($questionId);
			if ($this->questionRepository->delete($question)) {
				$this->flashMessage('Question deleted!', 'success');
			}
		} catch (EntityNotFoundException $e) {
			$this->error('Question not found!');
		}
	}

	public function createComponentQuestionsGrid(): void
	{
		$grid = $this->addGrid('questionsGrid', $this->questionRepository->getDataSource(['election_id' => $this->election->getId()]), 'questions')
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::FILTERTEXT, 'name', 'Name')
			->addColumn(Column::FILTERTEXT, 'question', 'Question')
			->addColumn(Column::BOOL, 'required', 'Required')
			->addColumn(Column::NUMBER, 'min', 'min')
			->addColumn(Column::NUMBER, 'max', 'max')
			->addAction(Action::EDIT, 'editQuestion!', ['questionId' => 'id'])
			->addAction(Action::DELETE, 'deleteQuestion!', ['questionId' => 'id'])
			->addToolbarButton(ToolbarButton::ADD, 'Add new question', 'showQuestionForm!');
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
		$form->onCancel = function() {
			$this->flashMessage('canceled');
			$this->template->showQuestionForm = false;
		};
		$form->onSuccess = function () {
			$this->redirect('this');
		};
		$form->onRefresh = function () {
			$this->template->showQuestionForm = true;
		};
		return $form;
	}

	/* ANSWERS */

	/**
	 * @restricted
	 * @resource(answers)
	 * @privilege(view)
	 */
	public function renderAnswers(): void
	{
		$this->template->selectedTab = 'answers';
	}

	/**
	 * @restricted
	 * @resource(answers)
	 * @privilege(delete)
	 */
	public function handleDeleteAnswer(int $answerId): void
	{
		if ($this->election->isRunning()) {
			$this->flashMessage('Cannot change running election!', 'error');
			return;
		}
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

	public function createComponentAnswersGrid(): void
	{
		$this->addGrid('answersGrid', $this->answerRepository->getDataSource(['election_id' => $this->election->getId()]), 'answers')
			->addColumn(Column::TEXT, 'question_id', 'question id')
			->addColumn(Column::TEXT, 'question', 'question text')
			->addColumn(Column::TEXT, 'value', 'answer')
			->addAction(Action::DELETE, 'deleteAnswer!', ['answerId' => 'id']);
	}

	/* VOTER FILES */

	/**
	 * @restricted
	 * @resource(voterFiles)
	 * @privilege(view)
	 */
	public function renderVoterFiles(): void
	{
		$this->template->voterFiles = $this->voterFileRepository->findRelated($this->election);
		$this->template->selectedTab = 'voterFiles';
	}

	/**
	 * @restricted
	 * @resource(voterFiles)
	 * @privilege(delete)
	 */
	public function handleDeleteVoterFile(int $voterFileId): void
	{
		if ($this->election->isRunning()) {
			$this->flashMessage('Cannot change running election!', 'error');
			return;
		}
		try {
			$voterFile = $this->voterFileRepository->findById($voterFileId);
			if ($this->voterFileRepository->delete($voterFile)) {
				$this->flashMessage('Voter file ' . $voterFile->filename . ' deleted', 'success');
				$this->getGrid('voterFilesGrid')->reload();
			}
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('Election wasn\'t found!', 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(voterFiles)
	 * @privilege(detail)
	 */
	public function handleShowVoterFileDetail(int $voterFileId): void
	{
		$this->template->voterFileDetail = $this->voterFileRepository->findById($voterFileId);
		$this->template->showVoterFileDetail = true;
		$this->template->showModal = true;
		$this->template->modalTitle = 'Voter file detail';
		$this->payload->showModal = true;
		$this->payload->modalId = 'myModal';
		$this->template->modalControl = 'voterFileDetailGrid';
		if ($this->isAjax()) {
			$this->redrawControl('modal');
		}
	}

	/**
	 * @restricted
	 * @resource(voterFiles)
	 * @privilege(import)
	 */
	public function handleImportVoterList(): void
	{
		if ($this->election->isRunning()) {
			$this->flashMessage('Cannot change running election!', 'error');
			return;
		}
		$this->template->showImportVoterListForm = true;
		$this->template->showModal = true;
		$this->template->modalTitle = 'Import voter list';
		$this->payload->showModal = true;
		$this->payload->modalId = 'myModal';
		$this->template->modalControl = 'importVoterListForm';
		if ($this->isAjax()) {
			// $this->redrawControl('cardSnippet');
			$this->redrawControl('modal');
			$this->redrawControl('scripts');
		}
	}

	/**
	 * @restricted
	 * @resource(voterFiles)
	 * @privilege(download)
	 */
	public function handleDownloadVoterFile(int $voterFileId): void
	{
		if ($this->isAjax()) {
			$this->redirectUrl($this->link('downloadVoterFile!', ['voterFileId' => $voterFileId]), 302);
		}
		try {
			$voterFile = $this->voterFileRepository->findById($voterFileId);
			$content = $voterFile->getContent();
			$this->sendResponse(new CsvResponse($voterFile->filename, $voterFile->content));
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('Voter file not found', 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(voterFiles)
	 * @privilege(apply)
	 */
	public function handleApplyVoterFile(int $voterFileId): void
	{
		if ($this->election->isRunning()) {
			$this->flashMessage('Cannot change running election!', 'error');
			return;
		}
		try {
			$voterFile = $this->voterFileRepository->findById($voterFileId);
			$this->voterRepository->importFromFile($this->election, $voterFile);
			$this->flashMessage('Voter file applied!', 'success');
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('Voter file not found!', 'error');
		}
	}

	public function createComponentVoterFilesGrid(): void
	{
		$this->addGrid('voterFilesGrid', $this->voterFileRepository->getDataSource(['election_id' => $this->getParameter('id')]), 'voterFiles')
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
		$fp = fopen('php://temp', 'rb+');
		fputs($fp, $content);
		rewind($fp);
		$csv = [];
		while ($line = fgetcsv($fp)) {
			$csv[] = $line;
		}
		$datasource = new ArrayDataSource($csv);
		$this->addGrid('voterFileDetailGrid', $datasource, null, '2')
			->addColumn(Column::TEXT, '0', 'name')
			->addColumn(Column::TEXT, '1', 'surname')
			->addColumn(Column::TEXT, '2', 'email');
		$this->template->showVoterFileDetail = true;
		$this->template->selectedTab = 'voterList';
		if ($this->isAjax()) {
			$this->redrawControl('cardSnippet');
		}
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
		if ($this->election->isRunning()) {
			$this->flashMessage('Cannot change running election!', 'error');
			return;
		}
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

	/* VOTER LIST */

	/**
	 * @restricted
	 * @resource(voterList)
	 * @privilege(view)
	 */
	public function renderVoterList(): void
	{
		$this->template->selectedTab = 'voterList';
	}

	public function createComponentVoterListGrid(): void
	{
		$this->addGrid('voterListGrid', $this->voterRepository->getDataSource(['election_id' => $this->election->getId()]))
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::FILTERTEXT, 'email', 'Email')
			->addColumn(Column::BOOL, 'voted', 'voted')
			->addColumn(Column::DATETIME, 'timestamp', 'timestamp');
	}
}
