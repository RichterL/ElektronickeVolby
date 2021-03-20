<?php

declare(strict_types=1);

namespace App\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Models\Entities\Role\Role;
use Models\Tables;
use Nette;
use Nette\Application\UI\Form;
use Repositories\RoleRepository;

final class RolesPresenter extends DefaultPresenter
{
	private Nette\Database\Explorer $database;
	private RoleRepository $repository;

	public function __construct(RoleRepository $roleRepository, Nette\Database\Explorer $database)
	{
		$this->repository = $roleRepository;
		$this->database = $database;
	}

	public function actionDefault()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function handleEdit(int $roleId)
	{
		$this->template->showRoleForm = true;
		$role = $this->repository->findById($roleId);
		//$role = $this->database->table(Tables::ACL_ROLES)->get($roleId);
		if (!$role) {
			$this->error('Role not found');
		}
		$this['roleForm']->setDefaults($role->toArray());
		$this->redrawControl('roleFormSnippet');
	}

	public function renderDefault(): void
	{
		$roles = $this->repository->findAll();
		//$roles = $this->database->table('acl_roles')->fetchAll();
		$this->template->roles = $roles;
	}

	public function handleShowRoleForm()
	{
		$this->template->showRoleForm = true;
		$this->redrawControl('roleFormSnippet');
	}

	public function createComponentRoleForm(): Form
	{
		$form = new BootstrapForm();
		$form->renderMode = RenderMode::SIDE_BY_SIDE_MODE;
		$form->addHidden('id');
		$form->addText('name', 'Name')->setRequired();
		$form->addText('key', 'Key')->setRequired();
		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = [$this, 'roleFormSuccess'];
		return $form;
	}

	public function roleFormSuccess(Form $form, array $values): void
	{
		$roleId = (int) $values['id'];
		unset($values['id']);
		if ($roleId) {
			$role = $this->repository->findById($roleId);
		//$role = $this->database->table(Tables::ACL_ROLES)->get($roleId);
			//$role->update($values);
		} else {
			$role = new Role();
			//$this->database->table('acl_roles')->insert($values);
		}
		$role->setValues($values);
		if ($this->repository->save($role)) {
			$this->flashMessage('Role saved.');
		}
	}
}
