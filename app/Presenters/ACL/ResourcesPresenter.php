<?php

declare(strict_types=1);

namespace App\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Models\Entities\Resource\Privilege;
use Models\Entities\Resource\PrivilegeAlreadyExistsException;
use Models\Entities\Resource\Resource;
use Nette;
use Nette\Application\UI\Form;
use Repositories\ResourceRepository;
use Repositories\PrivilegeRepository;

final class ResourcesPresenter extends DefaultPresenter
{
	private ResourceRepository $repository;
	private PrivilegeRepository $privilegeRepository;

	public function __construct(ResourceRepository $resourceRepository, PrivilegeRepository $privilegeRepository)
	{
		$this->repository = $resourceRepository;
		$this->privilegeRepository = $privilegeRepository;
	}

	public function actionDefault()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function actionEdit(int $resourceId)
	{
	}

	public function renderDefault(): void
	{
		$resources = $this->repository->findAll();
		$this->template->resources = $resources;
	}

	public function renderEdit(int $resourceId)
	{
		//$this->template->showResourceForm = true;
		$resource = $this->repository->findById($resourceId);
		if (!$resource) {
			$this->error('Resource not found');
		}
		$this->template->resource = $resource;
		//$this->template->privileges = $this->privilegeRepository->findByResource($resource);
		//$this['resourceForm']->setDefaults($resource->toArray());
	}

	public function handleEditResource()
	{
		$resourceId = (int) $this->getParameter('resourceId');
		$resource = $this->repository->findById($resourceId);
		if (!$resource) {
			$this->error('Resource not found');
		}
		$this->handleShowResourceForm();
		$this->getForm('resourceForm')->setDefaults($resource->toArray());
	}

	public function handleEditPrivilege(int $privilegeId)
	{
		$privilege = $this->privilegeRepository->findById($privilegeId);
		if (!$privilege) {
			$this->error('Privilege not found');
		}
		$this->handleShowPrivilegeForm();
		$this['privilegeForm']->setDefaults($privilege->toArray());
	}

	public function createComponentResourceForm(): Form
	{
		$form = new BootstrapForm();
		$form->addHidden('id');
		$form->addText('name', 'Name')->setRequired();
		$form->addText('key', 'Key')->setRequired();
		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = [$this, 'resourceFormSuccess'];

		return $form;
	}

	public function resourceFormSuccess(Form $form, array $values): void
	{
		$resourceId = (int) $values['id'];
		unset($values['id']);
		if ($resourceId) {
			$resource = $this->repository->findById($resourceId);
		} else {
			$resource = new Resource();
		}
		$resource->setValues($values);
		if ($this->repository->save($resource)) {
			$this->flashMessage('Resource saved.');
		}
		//$this->redrawControl('resourceFormSnippet');
	}

	public function handleShowResourceForm()
	{
		$this->template->showResourceForm = true;
		$this->redrawControl('resourceFormSnippet');
	}

	public function createComponentPrivilegeForm(): Form
	{
		$form = new BootstrapForm();
		$form->addHidden('id');
		$form->addHidden('resourceId')->setValue($this->getParameter('resourceId'));
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
		$resource = $this->repository->findById($resourceId);
		if ($this->privilegeRepository->save($resource, $privilege)) {
			$this->flashMessage('Privilege saved.');
		}
	}

	public function handleShowPrivilegeForm()
	{
		$this->template->showPrivilegeForm = true;
		$this->redrawControl('privilegeFormSnippet');
	}
}
