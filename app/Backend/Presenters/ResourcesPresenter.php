<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use App\Models\Entities\Resource\Privilege;
use App\Models\Entities\Resource\Resource;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use App\Repositories\ResourceRepository;
use App\Repositories\PrivilegeRepository;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use App\Backend\Utils\DataGrid\Action;
use App\Backend\Utils\DataGrid\Column;
use App\Backend\Utils\DataGrid\ToolbarButton;

final class ResourcesPresenter extends BasePresenter
{
	private ResourceRepository $resourceRepository;
	private PrivilegeRepository $privilegeRepository;

	public function __construct(ResourceRepository $resourceRepository, PrivilegeRepository $privilegeRepository)
	{
		parent::__construct();
		$this->resourceRepository = $resourceRepository;
		$this->privilegeRepository = $privilegeRepository;
	}

	public function actionDefault(): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	/**
	 * @restricted
	 * @resource(resources)
	 * @privilege(edit)
	 */
	public function actionEdit(int $id): void
	{
	}

	public function renderDefault(): void
	{
		$resources = $this->resourceRepository->findAll();
		$this->template->resources = $resources;
	}

	public function renderEdit(int $id): void
	{
		try {
			$resource = $this->resourceRepository->findById($id);
			$this->template->resource = $resource;
		} catch (EntityNotFoundException $e) {
			$this->error('Resource not found');
		}
	}

	public function createComponentResourcesGrid(): void
	{
		$this->addGrid('resourcesGrid', $this->resourceRepository->getDataSource(), 'resources')
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::TEXT, 'name', 'Name')
			->addColumn(Column::TEXT, 'key', 'Key')
			->addAction(Action::VIEW, ':edit', null, false)
			->addAction(Action::EDIT, 'editResource!')
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete resource %s?', 'name'), 'deleteResource!')
			->addToolbarButton(ToolbarButton::ADD, 'Add new resource', 'showResourceForm!');
	}

	public function createComponentPrivilegesGrid(): void
	{
		$resources = $this->resourceRepository->getIdNamePairs();
		$this->addGrid('privilegesGrid', $this->privilegeRepository->getDataSource(['resource_id' => $this->getParameter('id')]), 'resources')
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::TEXT_MULTISELECT, 'resource_id', 'Resource', $resources)
			->addColumn(Column::FILTERTEXT, 'name', 'Name')
			->addColumn(Column::FILTERTEXT, 'key', 'Key')
			->addAction(Action::EDIT, 'editPrivilege!', ['privilegeId' => 'id'])
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete privilege %s?', 'name'), 'deletePrivilege!', ['privilegeId' => 'id'])
			->addToolbarButton(ToolbarButton::ADD, 'Add new privilege', 'showPrivilegeForm!');
	}

	/**
	 * @restricted
	 * @resource(resources)
	 * @privilege(edit)
	 */
	public function handleEditResource(int $id): void
	{
		try {
			$resource = $this->resourceRepository->findById($id);
			$this->handleShowResourceForm();
			$this->getForm('resourceForm')->setDefaults($resource->toArray());
			$this->template->resourceEdit = true;
		} catch (EntityNotFoundException $e) {
			$this->error('Resource not found');
		}
	}

	/**
	 * @restricted
	 * @resource(resources)
	 * @privilege(delete)
	 */
	public function handleDeleteResource(int $id): void
	{
		try {
			$resource = $this->resourceRepository->findById($id);
			$this->resourceRepository->delete($resource);
			$this->flashMessage('Role id ' . $id . ' deleted', 'success');
			$this->getGrid('resourcesGrid')->reload();
		} catch (EntityNotFoundException $e) {
			$this->error('Resource not found!');
		} catch (DeletingErrorException $e) {
			$this->flashMessage('Deleting failed', 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(resources)
	 * @privilege(edit)
	 */
	public function handleEditPrivilege(int $privilegeId): void
	{
		try {
			$privilege = $this->privilegeRepository->findById($privilegeId);
			$this->handleShowPrivilegeForm();
			$this->template->editPrivilege = true;
			$this['privilegeForm']->setDefaults($privilege->toArray());
		} catch (EntityNotFoundException $e) {
			$this->error('Privilege not found');
		}
	}

	/**
	 * @restricted
	 * @resource(resources)
	 * @privilege(delete)
	 */
	public function handleDeletePrivilege(int $privilegeId): void
	{
		try {
			$privilege = $this->privilegeRepository->findById($privilegeId);
			$this->privilegeRepository->delete($privilege);
			$this->flashMessage('Privilege id ' . $privilegeId . ' deleted', 'success');
			$this->getGrid('privilegesGrid')->reload();
		} catch (EntityNotFoundException $e) {
			$this->error('Resource not found!');
		} catch (DeletingErrorException $e) {
			$this->flashMessage('Deleting failed', 'error');
		}
	}

	public function createComponentResourceForm(): Form
	{
		$form = new BootstrapForm();
		$form->setRenderMode(RenderMode::SIDE_BY_SIDE_MODE);
		$form->addHidden('id');
		$form->addText('name', 'Name')->setRequired();
		$form->addText('key', 'Key')->setRequired();
		$form->addSubmit('submit', 'Save');
		$form->addButton(
			'Cancel',
			Html::el('a')
				->href($this->link('hideResourceForm!'))
				->data('naja-history', 'off')
				->class('ajax text-white')
				->setHtml('Cancel')
		)->setBtnClass('btn-danger');
		$form->onSuccess[] = [$this, 'resourceFormSuccess'];
		return $form;
	}

	public function resourceFormSuccess(Form $form, array $values): void
	{
		try {
			$resourceId = (int) $values['id'];
			unset($values['id']);
			$resource = $resourceId ? $this->resourceRepository->findById($resourceId) : new Resource();
			$resource->setValues($values);
			$this->resourceRepository->save($resource);
			$this->flashMessage('Resource saved.', 'success');
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('Privilege not found!', 'error');
		} catch (SavingErrorException $e) {
			$this->flashMessage('Saving failed', 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(resources)
	 * @privilege(edit)
	 */
	public function handleShowResourceForm(): void
	{
		$this->template->showResourceForm = true;
		$this->redrawControl('resourceFormSnippet');
	}

	public function handleHideResourceForm(): void
	{
		$this->template->showResourceForm = false;
		$this->redrawControl('resourceFormSnippet');
	}

	public function createComponentPrivilegeForm(): Form
	{
		$form = new BootstrapForm();
		$form->addHidden('id');
		$form->addHidden('resourceId')->setDefaultValue($this->getParameter('id'));
		$form->addText('name', 'Name')->setRequired();
		$form->addText('key', 'Key')->setRequired();
		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = [$this, 'privilegeFormSuccess'];
		return $form;
	}

	public function privilegeFormSuccess(Form $form, array $values): void
	{
		$privilegeId = (int) $values['id'];
		$resourceId = (int) $values['resourceId'];
		unset($values['id'], $values['resourceId']);

		if ($privilegeId) {
			$privilege = $this->privilegeRepository->findById($privilegeId);
		} else {
			$privilege = new Privilege();
		}
		$privilege->setValues($values);
		try {
			$resource = $this->resourceRepository->findById($resourceId);
			$this->privilegeRepository->save($resource, $privilege);
			$this->flashMessage('Privilege saved.', 'success');
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('resource not found', 'error');
		} catch (SavingErrorException $e) {
			$this->flashMessage('saving failed', 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(resources)
	 * @privilege(edit)
	 */
	public function handleShowPrivilegeForm(): void
	{
		$this->template->showPrivilegeForm = true;
		$this->redrawControl('privilegeFormSnippet');
	}

	public function handleHidePrivilegeForm(): void
	{
		$this->template->showPrivilegeForm = false;
		$this->redrawControl('privilegeFormSnippet');
	}
}
