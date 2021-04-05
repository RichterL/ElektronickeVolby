<?php
declare(strict_types=1);

namespace App\Backend\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Models\Entities\Election\Answer;
use Models\Entities\Election\Election;
use Models\Entities\Election\Question;
use Models\Entities\Election\VoterFile;
use Nette\Application\Responses\CsvResponse;
use Nette\Application\UI\Form;
use Repositories\AnswerRepository;
use Repositories\ElectionRepository;
use Repositories\QuestionRepository;
use Repositories\UserRepository;
use Repositories\VoterFileRepository;
use Repositories\VoterRepository;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataSource\ArrayDataSource;
use Utils\DataGrid\Action;
use Utils\DataGrid\Column;

final class ElectionPresenter extends DefaultPresenter
{
	/** @persistent */
	public $id;
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
		$this->electionRepository = $electionRepository;
		$this->userRepository = $userRepository;
		$this->voterFileRepository = $voterFileRepository;
		$this->voterRepository = $voterRepository;
		$this->questionRepository = $questionRepository;
		$this->answerRepository = $answerRepository;
	}

	public function startup()
	{
		parent::startup();
		$election = $this->electionRepository->findById((int) $this->getParameter('id'));
		if (!$election) {
			$this->error('Election not found!');
		}
		$this->election = $election;
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->setFile(__DIR__ . '/templates/Election/default.latte');
		$this->template->election = $this->election;
	}

	public function afterRender()
	{
		parent::afterRender();
		if ($this->isAjax()) {
			$this->redrawControl('cardSnippet');
		}
	}

	public function renderOverview()
	{
		$this->template->selectedTab = 'overview';
	}

	public function renderQuestions()
	{
		$this->template->selectedTab = 'questions';
		$questions = $this->questionRepository->findRelated($this->election);
		$this->template->questions = $questions;
	}

	public function renderAnswers()
	{
		$this->template->selectedTab = 'answers';
	}

	public function renderVoterFiles()
	{
		$this->template->voterFiles = $this->voterFileRepository->findRelated($this->election);
		$this->template->selectedTab = 'voterFiles';
	}

	public function renderVoterList()
	{
		$this->template->selectedTab = 'voterList';
	}

	public function handleDeleteVoterFile(int $voterFileId)
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

	public function handleShowVoterFileDetail(int $voterFileId)
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

	public function handleImportVoterList()
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

	public function handleDownloadVoterFile(int $voterFileId)
	{
		if ($this->isAjax()) {
			$this->redirectUrl($this->link('downloadVoterFile!', ['voterFileId' => $voterFileId]), 302);
		}
		$voterFile = $this->voterFileRepository->findById($voterFileId);
		$content = $voterFile->getContent();
		$this->sendResponse(new CsvResponse($voterFile->filename, $voterFile->content));
	}

	public function handleApplyVoterFile(int $voterFileId)
	{
		$voterFile = $this->voterFileRepository->findById($voterFileId);
		$this->voterRepository->importFromFile($this->election, $voterFile);
		$this->flashMessage('Voter file applied!', 'success');
	}

	public function handleDeleteQuestion(int $questionId)
	{
		$question = $this->questionRepository->findById($questionId);
		if (!$question) {
			$this->error('Question not found!');
		}

		if ($this->questionRepository->delete($question)) {
			$this->flashMessage('Question deleted!', 'success');
		}
	}

	public function createComponentVoterFilesGrid()
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

	public function createComponentVoterFileDetailGrid()
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

	public function createComponentVoterListGrid()
	{
		$this->addGrid('voterListGrid', $this->voterRepository->getDataSource(['election_id' => $this->election->getId()]))
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::FILTERTEXT, 'email', 'Email')
			->addColumn(Column::BOOL, 'voted', 'voted')
			->addColumn(Column::DATETIME, 'timestamp', 'timestamp');
	}

	public function createComponentQuestionsGrid()
	{
		$grid = $this->addGrid('questionsGrid', $this->questionRepository->getDataSource(['election_id' => $this->election->getId()]))
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::FILTERTEXT, 'name', 'Name')
			->addColumn(Column::FILTERTEXT, 'question', 'Question')
			->addColumn(Column::BOOL, 'required', 'Required')
			->addColumn(Column::BOOL, 'multiple', 'Multiple')
			->addAction(Action::DELETE, 'deleteQuestion!', ['questionId' => 'id'])
			->getOriginal();
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

	public function createComponentAnswersGrid()
	{
		$this->addGrid('answersGrid', $this->answerRepository->getDataSource(['election_id' => $this->election->getId()]))
			->addColumn(Column::TEXT, 'question');
	}

	public function createComponentImportVoterListForm()
	{
		$form = new BootstrapForm();
		$form->setRenderMode(RenderMode::SIDE_BY_SIDE_MODE);
		$form->setAjax();
		$form->addUpload('file', 'Upload')->setHtmlAttribute('accept', '.csv');
		$form->addSubmit('submit', 'Import')->setHtmlAttribute('data-dismiss', 'modal');
		$form->onSuccess[] = [$this, 'importVoterListFormSuccess'];
		return $form;
	}

	public function importVoterListFormSuccess(Form $form, array $values)
	{
		/** @var \Nette\Http\FileUpload */
		$file = $values['file'];
		unset($values['file']);

		$values['filename'] = $file->getSanitizedName();
		$values['content'] = $file->getContents();
		$values['createdAt'] = new \DateTime();
		$values['createdBy'] = $this->userRepository->findById($this->getLoggedUserId(), false);
		$voterFile = new VoterFile();
		$voterFile->setElection($this->electionRepository->findById((int) $this->getParameter('id')));
		$voterFile->setValues($values);
		if ($this->voterFileRepository->save($voterFile)) {
			$this->flashMessage('import success', 'success');
		// $this->redrawControl('modals');
		} else {
			$this->flashMessage('import failed', 'error');
		}
		$this->redirect(':voterFiles', ['id' => (int) $this->getParameter('id')]);

		// $contents = $file->getContents();
		// file_put_contents(TEMP_DIR . '/encoded.gz', gzencode($contents, 9));

		// $h = fopen($file->getTemporaryFile(), 'r');
		// $lines = [];
		// while ($line = fgetcsv($h)) {
		// 	$lines[] = $line;
		// }
		// $this->template->lines = $lines;
	}

	/** @var \App\Forms\Election\QuestionFormFactory @inject */
	public $questionFormFactory;

	public function createComponentQuestionForm()
	{
		$form = $this->questionFormFactory->create();
		$form->onBeforeSave = function (\Nette\Forms\Form $form, array $values) {
			// check any conditions before saving the form
			// stop saving process by $form->addError()
		};
		// $form->onSave = [$this, 'formSuccess']; // this is not necessary probably
		$form->onAfterSave = function (\Nette\Forms\Form $form, array $values) {
			$this->flashMessage('saved');
			$this->redirect(':overview');
		};

		return $form;
	}
}
