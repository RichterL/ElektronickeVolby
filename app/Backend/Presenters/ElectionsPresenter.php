<?php
declare(strict_types=1);

namespace App\Backend\Presenters;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use App\Models\Entities\Election\Election;
use Nette\Application\UI\Form;
use App\Repositories\ElectionRepository;
use App\Repositories\UserRepository;
use Nette\InvalidStateException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use App\Backend\Utils\DataGrid\Action;
use App\Backend\Utils\DataGrid\Column;
use App\Backend\Utils\DataGrid\ToolbarButton;

final class ElectionsPresenter extends BasePresenter
{
	private ElectionRepository $electionRepository;
	private UserRepository $userRepository;

	public function __construct(ElectionRepository $electionRepository, UserRepository $userRepository)
	{
		parent::__construct();
		$this->electionRepository = $electionRepository;
		$this->userRepository = $userRepository;
	}

	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(view)
	 */
	public function renderDefault(): void
	{
		$this->template->elections = $this->electionRepository->findAll();
	}

	public function createComponentElectionsGrid(): void
	{
		$this->addGrid('electionsGrid', $this->electionRepository->getDataSource(), 'elections')
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
		$form->addTextArea('description', 'Description')->setRequired()->setHtmlId('textareaInput');
//		$form->addCheckbox('secret', 'Secret');	
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
			if (!empty($electionId)) {
				$election = $this->electionRepository->findById($electionId);
			} else {
				$election = new Election();
				$election = $election->withSigningKey();
			}
			$values['createdAt'] = new \DateTime();
			$values['createdBy'] = $this->userRepository->findById($this->getLoggedUserId(), false);
			$election->setValues($values);
			$this->electionRepository->save($election);
			if ($this->isAjax()) {
				$this->handleHideElectionForm();
				$this->flashMessage('election saved.');
				$electionId ? $this->getGrid('electionsGrid')->redrawItem($electionId) : $this->getGrid('electionsGrid')->reload();
			} else {
				$this->redirect('this');
			}
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('Election wasn\'t found!', 'error');
		} catch (SavingErrorException $e) {
			$this->flashMessage('saving failed!', 'error');
		} catch (InvalidStateException $e) {
			$this->flashMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(edit)
	 */
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

	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(delete)
	 */
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

	/**
	 * @restricted
	 * @resource(elections)
	 * @privilege(edit)
	 */
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
