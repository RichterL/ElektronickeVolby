<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

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

final class ResourcesPresenter extends DefaultPresenter
{
	private ResourceRepository $resourceRepository;
	private PrivilegeRepository $privilegeRepository;

	public function __construct(ResourceRepository $resourceRepository, PrivilegeRepository $privilegeRepository)
	{
		$this->resourceRepository = $resourceRepository;
		$this->privilegeRepository = $privilegeRepository;
	}

	public function actionDefault()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function actionEdit(int $id)
	{
	}

	public function renderDefault(): void
	{
		$resources = $this->resourceRepository->findAll();
		$this->template->resources = $resources;
	}

	public function renderEdit(int $id)
	{
		$resource = $this->resourceRepository->findById($id);
		if (!$resource) {
			$this->error('Resource not found');
		}
		$this->template->resource = $resource;
	}

	public function createComponentResourcesGrid()
	{
		$this->addGrid('resourcesGrid', $this->resourceRepository->getDataSource())
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::TEXT, 'name', 'Name')
			->addColumn(Column::TEXT, 'key', 'Key')
			->addAction(Action::VIEW, ':edit', null, false)
			->addAction(Action::EDIT, 'editResource!')
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete resource %s?', 'name'), 'deleteResource!')
			->addToolbarButton(ToolbarButton::ADD, 'Add new resource', 'showResourceForm!');
	}

	public function createComponentPrivilegesGrid()
	{
		$resources = $this->resourceRepository->getIdNamePairs();
		$this->addGrid('privilegesGrid', $this->privilegeRepository->getDataSource(['resource_id' => $this->getParameter('id')]))
			->addColumn(Column::NUMBER, 'id', 'id')
			->addColumn(Column::TEXT_MULTISELECT, 'resource_id', 'Resource', $resources)
			->addColumn(Column::FILTERTEXT, 'name', 'Name')
			->addColumn(Column::FILTERTEXT, 'key', 'Key')
			->addAction(Action::EDIT, 'editPrivilege!', ['privilegeId' => 'id'])
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete privilege %s?', 'name'), 'deletePrivilege!', ['privilegeId' => 'id'])
			->addToolbarButton(ToolbarButton::ADD, 'Add new privilege', 'showPrivilegeForm!');
	}

	public function handleEditResource(int $id)
	{
		$resource = $this->resourceRepository->findById($id);
		if (!$resource) {
			$this->error('Resource not found');
		}
		$this->handleShowResourceForm();
		$this->getForm('resourceForm')->setDefaults($resource->toArray());
		$this->template->resourceEdit = true;
	}

	public function handleDeleteResource(int $id)
	{
		$resource = $this->resourceRepository->findById($id);
		if (!$resource) {
			$this->error('Resource not found!');
		}
		if ($this->resourceRepository->delete($resource)) {
			$this->flashMessage('Role id ' . $id . ' deleted', 'success');
			$this->getGrid('resourcesGrid')->reload();
		}
	}

	public function handleEditPrivilege(int $privilegeId)
	{
		$privilege = $this->privilegeRepository->findById($privilegeId);
		if (!$privilege) {
			$this->error('Privilege not found');
		}
		$this->handleShowPrivilegeForm();
		$this->template->editPrivilege = true;
		$this['privilegeForm']->setDefaults($privilege->toArray());
	}

	public function handleDeletePrivilege(int $privilegeId)
	{
		$privilege = $this->privilegeRepository->findById($privilegeId);
		if (!$privilege) {
			$this->error('Resource not found!');
		}
		if ($this->privilegeRepository->delete($privilege)) {
			$this->flashMessage('Privilege id ' . $privilegeId . ' deleted', 'success');
			$this->getGrid('privilegesGrid')->reload();
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
		$form->addButton('Cancel', Html::el('a')->href($this->link('hideResourceForm!'))->class('ajax text-white')->setHtml('Cancel'))->setBtnClass('btn-danger');
		$form->onSuccess[] = [$this, 'resourceFormSuccess'];

		return $form;
	}

	public function resourceFormSuccess(Form $form, array $values): void
	{
		$resourceId = (int) $values['id'];
		unset($values['id']);
		if ($resourceId) {
			$resource = $this->resourceRepository->findById($resourceId);
		} else {
			$resource = new Resource();
		}
		$resource->setValues($values);
		if ($this->resourceRepository->save($resource)) {
			$this->flashMessage('Resource saved.', 'success');
		}
		//$this->redrawControl('resourceFormSnippet');
	}

	public function handleShowResourceForm()
	{
		$this->template->showResourceForm = true;
		$this->redrawControl('resourceFormSnippet');
	}

	public function handleHideResourceForm()
	{
		$this->template->showResourceForm = false;
		$this->redrawControl('resourceFormSnippet');
	}

	public function createComponentPrivilegeForm(): Form
	{
		$form = new BootstrapForm();
		$form->addHidden('id');
		$form->addHidden('resourceId')->setValue($this->getParameter('id'));
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
		unset($values['id'], $values['resrouceId']);

		if ($privilegeId) {
			$privilege = $this->privilegeRepository->findById($privilegeId);
		} else {
			$privilege = new Privilege();
		}
		$privilege->setValues($values);
		$resource = $this->resourceRepository->findById($resourceId);
		if ($this->privilegeRepository->save($resource, $privilege)) {
			$this->flashMessage('Privilege saved.', 'success');
		}
	}

	public function handleShowPrivilegeForm()
	{
		$this->template->showPrivilegeForm = true;
		$this->redrawControl('privilegeFormSnippet');
	}

	public function handleHidePrivilegeForm()
	{
		$this->template->showPrivilegeForm = false;
		$this->redrawControl('privilegeFormSnippet');
	}
}
