<?php
declare(strict_types=1);

namespace App\Backend\Presenters;

use Constants;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Models\Entities\Election\Election;
use Nette\Application\UI\Form;
use Repositories\ElectionRepository;
use Repositories\UserRepository;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Utils\DataGrid\Action;
use Utils\DataGrid\Column;
use Utils\DataGrid\ToolbarButton;

final class ElectionPresenter extends DefaultPresenter
{
	private ElectionRepository $electionRepository;
	private UserRepository $userRepository;

	public function __construct(ElectionRepository $electionRepository, UserRepository $userRepository)
	{
		$this->electionRepository = $electionRepository;
		$this->userRepository = $userRepository;
	}

	public function renderDefault()
	{
		$this->template->elections = $this->electionRepository->findAll();
	}

	public function renderView(int $id)
	{
		$election = $this->electionRepository->findById($id);
		$this->template->election = $election;
	}

	public function createComponentElectionsGrid()
	{
		$this->addGrid('electionsGrid', $this->electionRepository->getDataSource())
			->addColumn(Column::TEXT, 'title', 'Title')
			->addColumn(Column::BOOL, 'active', 'Active')
			->addColumn(Column::BOOL, 'secret', 'Secret')
			->addColumn(Column::DATETIME, 'start', 'Start')
			->addColumn(Column::DATETIME, 'end', 'End')
			->addAction(Action::VIEW, ':view')
			->addAction(Action::EDIT, 'edit!')
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete election %s?', 'title'), 'delete!')
			->addToolbarButton(ToolbarButton::ADD, 'Create new election', 'showElectionForm!');
	}

	public function createComponentElectionForm($name)
	{
		$form = new BootstrapForm();
		$form->setRenderMode(RenderMode::SIDE_BY_SIDE_MODE);
		$form->addHidden('id');
		$form->addText('title', 'Title');
		$form->addTextArea('description', 'Description');
		$form->addCheckbox('active', 'Active');
		$form->addCheckbox('secret', 'Secret');
		$form->addDateTime('start', 'Start');
		$form->addDateTime('end', 'End');
		$form->addSubmit('submit', 'Submit')->setBtnClass('ajax btn-primary');
		$form->onSuccess[] = [$this, 'electionFormSuccess'];
		return $form;
	}

	public function electionFormSuccess(Form $form, array $values)
	{
		$electionId = (int) $values['id'];
		unset($values['id']);
		$election = ($electionId) ? $this->electionRepository->findById($electionId) : new Election();
		$values['createdAt'] = new \DateTime();
		$values['createdBy'] = $this->userRepository->findById($this->getLoggedUserId(), false);
		$election->setValues($values);
		$success = $this->electionRepository->save($election);
		if (!$success) {
			$this->flashMessage('saving failed!', 'error');
			return;
		}
		if ($this->isAjax()) {
			$this->handleHideElectionForm();
			$this->flashMessage('election saved.');
			$electionId ? $this->getGrid('electionsGrid')->redrawItem($electionId) : $this->getGrid('electionsGrid')->reload();
		} else {
			$this->redirect('this');
		}
	}

	public function handleShowElectionForm()
	{
		$this->template->showElectionForm = true;
		$this->redrawControl('electionFormSnippet');
	}

	public function handleHideElectionForm()
	{
		$this->template->showElectionForm = false;
		$this->redrawControl('electionFormSnippet');
	}

	public function handleDelete(int $id)
	{
		$election = $this->electionRepository->findById($id);
		if (!$election) {
			$this->flashMessage('Election wasn\'t found!', 'error');
			return;
		}
		if ($this->electionRepository->delete($election)) {
			$this->flashMessage('Election id ' . $id . ' deleted', 'success');
			$this->getGrid('electionsGrid')->reload();
		}
	}

	public function handleEdit(int $id)
	{
		$election = $this->electionRepository->findById($id);
		$values = $election->toArray();
		$form = $this->getForm('electionForm');
		$form->setValues($values);
		$this->template->editElection = true;
		$this->handleShowElectionForm();
	}

	public function handleShowOverview(int $id)
	{
		$this->template->selectedTab = 'overview';
		if ($this->isAjax()) {
			$this->redrawControl('cardSnippet');
		}
	}

	public function handleShowVoterList(int $id)
	{
		$this->template->voterList = ['John', 'Jane'];
		$this->template->selectedTab = 'voterList';
		if ($this->isAjax()) {
			$this->redrawControl('cardSnippet');
		}
	}
}
