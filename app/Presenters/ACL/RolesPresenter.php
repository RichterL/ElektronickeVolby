<?php

declare(strict_types=1);

namespace App\Presenters;

use Models\Tables;
use Nette;
use Nette\Application\UI\Form;

final class RolesPresenter extends Nette\Application\UI\Presenter
{
	private Nette\Database\Explorer $database;


	public function __construct(Nette\Database\Explorer $database) {
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
		$role = $this->database->table(Tables::ACL_ROLES)->get($roleId);
		if (!$role) {
			$this->error('Role not found');
		}
		$this['roleForm']->setDefaults($role->toArray());
		$this->redrawControl('roleFormSnippet');
	}

	public function renderDefault(): void
	{
		$roles = $this->database->table('acl_roles')->fetchAll();
		$this->template->roles = $roles;
	}

	public function handleShowRoleForm()
	{
		$this->template->showRoleForm = true;
		$this->redrawControl('roleFormSnippet');
	}

	public function createComponentRoleForm(): Form
	{
		$form = new Form();
		$form->addHidden('id');
		$form->addText('name', 'Name')->setRequired();
		$form->addText('key', 'Key')->setRequired();
		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = [$this, 'roleFormSuccess'];
		return $form;
	}

	public function roleFormSuccess(Form $form, array $values): void
	{
		$roleId = $values['id'];

		if ($roleId) {
			$role = $this->database->table(Tables::ACL_ROLES)->get($roleId);
			$role->update($values);
		} else {
			$this->database->table('acl_roles')->insert($values);
		}

	}
}
