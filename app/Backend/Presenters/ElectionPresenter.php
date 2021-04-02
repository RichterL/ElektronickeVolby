<?php
declare(strict_types=1);

namespace App\Backend\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Models\Entities\Election\Election;
use Models\Entities\Election\VoterFile;
use Nette\Application\Responses\CsvResponse;
use Nette\Application\UI\Form;
use Repositories\ElectionRepository;
use Repositories\UserRepository;
use Repositories\VoterFileRepository;
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

	public function __construct(ElectionRepository $electionRepository, UserRepository $userRepository, VoterFileRepository $voterFileRepository)
	{
		$this->electionRepository = $electionRepository;
		$this->userRepository = $userRepository;
		$this->voterFileRepository = $voterFileRepository;
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$election = $this->electionRepository->findById((int) $this->getParameter('id'));
		if (!$election) {
			$this->error('Election not found!');
		}
		$this->election = $election;
		$this->template->setFile(__DIR__ . '/templates/Election/default.latte');
		$this->template->election = $election;
	}

	public function renderOverview(int $id)
	{
		$this->template->selectedTab = 'overview';
		if ($this->isAjax()) {
			$this->redrawControl('cardSnippet');
		}
	}

	public function renderVoterFiles(int $id)
	{
		$this->template->voterFiles = $this->voterFileRepository->findRelated($this->election);
		$this->template->selectedTab = 'voterFiles';
		if ($this->isAjax()) {
			$this->redrawControl('cardSnippet');
		}
	}

	public function renderVoterList(int $id)
	{
		$this->template->selectedTab = 'voterList';
		if ($this->isAjax()) {
			$this->redrawControl('cardSnippet');
		}
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

	public function createComponentVoterFilesGrid()
	{
		$this->addGrid('voterFilesGrid', $this->voterFileRepository->getDataSource(['election_id' => $this->getParameter('id')]))
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::FILTERTEXT, 'filename', 'Filename')
			->addColumn(Column::DATETIME, 'created_at', 'Created at')
			->addColumn(Column::TEXT, 'created_by', 'Created by')
			->addAction(Action::VIEW, 'showVoterFileDetail!', ['voterFileId' => 'id'])
			->addAction(Action::DOWNLOAD, 'downloadVoterFile!', ['voterFileId' => 'id'], false)
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete voter file %s?', 'filename'), 'deleteVoterFile!', ['voterFileId' => 'id']);
		$this->getGrid('voterFilesGrid')->getAction('view')->addAttributes(['data-naja-history' => 'off']);
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
}
