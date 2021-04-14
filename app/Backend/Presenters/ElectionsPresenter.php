<?php
declare(strict_types=1);

namespace App\Backend\Presenters;

use App\Models\Mappers\Exception\EntityNotFoundException;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use App\Models\Entities\Election\Election;
use Nette\Application\UI\Form;
use App\Repositories\ElectionRepository;
use App\Repositories\UserRepository;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use App\Backend\Utils\DataGrid\Action;
use App\Backend\Utils\DataGrid\Column;
use App\Backend\Utils\DataGrid\ToolbarButton;

final class ElectionsPresenter extends DefaultPresenter
{
	private ElectionRepository $electionRepository;
	private UserRepository $userRepository;

	public function __construct(ElectionRepository $electionRepository, UserRepository $userRepository)
	{
		parent::__construct();
		$this->electionRepository = $electionRepository;
		$this->userRepository = $userRepository;
	}

	public function renderDefault(): void
	{
		$this->template->elections = $this->electionRepository->findAll();
	}

	public function createComponentElectionsGrid(): void
	{
		$this->addGrid('electionsGrid', $this->electionRepository->getDataSource())
			->addColumn(Column::TEXT, 'title', 'Title')
			->addColumn(Column::BOOL, 'active', 'Active')
			->addColumn(Column::BOOL, 'secret', 'Secret')
			->addColumn(Column::DATETIME, 'start', 'Start')
			->addColumn(Column::DATETIME, 'end', 'End')
			->addAction(Action::VIEW, 'Election:overview', null, false)
			->addAction(Action::EDIT, 'edit!')
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete election %s?', 'title'), 'delete!')
			->addToolbarButton(ToolbarButton::ADD, 'Create new election', 'showElectionForm!');
	}

	public function createComponentElectionForm($name): BootstrapForm
	{
		$form = new BootstrapForm();
		$form->setRenderMode(RenderMode::SIDE_BY_SIDE_MODE);
		$form->addHidden('id');
		$form->addText('title', 'Title')->setRequired();
		$form->addTextArea('description', 'Description')->setRequired();
//		$form->addCheckbox('active', 'Active');
		$form->addCheckbox('secret', 'Secret');
		$form->addDateTime('start', 'Start')->setRequired();
		$form->addDateTime('end', 'End')->setRequired();
		$form->addSubmit('submit', 'Submit')->setBtnClass('ajax btn-primary');
		$form->onSuccess[] = [$this, 'electionFormSuccess'];
		$form->onError[] = function (BootstrapForm $form) {
			$this->template->editElection = !empty($form->getUnsafeValues('array')['id']);
			$this->flashMessage('there were some errors in the form.', 'error');
			$this->handleShowElectionForm();
		};
		return $form;
	}

	public function electionFormSuccess(Form $form, array $values): void
	{
		try {
			$electionId = (int) $values['id'];
			unset($values['id']);
			$election = $electionId ? $this->electionRepository->findById($electionId) : new Election();
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
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('Election wasn\'t found!', 'error');
		}
	}

	public function handleShowElectionForm(): void
	{
		$this->template->showElectionForm = true;
		$this->redrawControl('electionFormSnippet');
	}

	public function handleHideElectionForm(): void
	{
		$this->template->showElectionForm = false;
		$this->redrawControl('electionFormSnippet');
	}

	public function handleDelete(int $id): void
	{
		try {
			$election = $this->electionRepository->findById($id);
			if ($this->electionRepository->delete($election)) {
				$this->flashMessage('Election id ' . $id . ' deleted', 'success');
				$this->getGrid('electionsGrid')->reload();
			}
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('Election wasn\'t found!', 'error');
		}
	}

	public function handleEdit(int $id): void
	{
		try {
			$election = $this->electionRepository->findById($id);
			$values = $election->toArray();
			$form = $this->getForm('electionForm');
			$form->setDefaults($values);
			$this->template->editElection = true;
			$this->handleShowElectionForm();
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('Election wasn\'t found!', 'error');
		}
	}
}
