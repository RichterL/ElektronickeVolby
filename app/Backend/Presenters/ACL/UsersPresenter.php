<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Models\Entities\User;
use Nette\Application\UI\Form;
use Repositories\RoleRepository;
use Repositories\UserRepository;
use Ublaboo\DataGrid\DataGrid;

final class UsersPresenter extends DefaultPresenter
{
	private UserRepository $userRepository;
	private RoleRepository $roleRepository;

	public function __construct(
		UserRepository $userRepository,
		RoleRepository $roleRepository
	) {
		$this->userRepository = $userRepository;
		$this->roleRepository = $roleRepository;
	}

	public function actionDefault()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function handleEdit(int $userId)
	{
		$this->template->showUserForm = true;
		$user = $this->userRepository->findById($userId);
		if (!$user) {
			$this->error('User not found');
		}
		$this['userForm']->setDefaults($user->toArray());
		$this->template->userEdit = true;
		$this->redrawControl('userFormSnippet');
	}

	public function renderDefault(): void
	{
		$users = $this->userRepository->findAll();
		$this->template->users = $users;
	}

	public function handleShowUserForm()
	{
		$this->template->showUserForm = true;
		$this->redrawControl('userFormSnippet');
	}

	public function createComponentUsersGrid(string $name)
	{
		$grid = new DataGrid($this, $name);
		$datasource = $this->userRepository->getDataSource();

		$grid->setDataSource($datasource);
		$grid->addColumnText('username', 'Username')
			->setFilterText();
		$grid->addColumnText('email', 'E-mail')
			->setFilterText();
		$grid->addColumnText('name', 'Name')
			->setFilterText();
		$grid->addColumnText('surname', 'Surname')
			->setFilterText();
		$grid->addColumnText('roles', 'Roles');
		$grid->addAction('edit', '', 'edit!', ['userId' => 'id'])
		->setIcon('edit')
		->setClass('btn btn-xs btn-warning ajax');
		$grid->addToolbarButton('showUserForm!', 'Add new user')
			->setClass('btn btn-sm btn-primary ajax');
	}

	public function createComponentUserForm(): Form
	{
		$form = new BootstrapForm();
		$form->renderMode = RenderMode::SIDE_BY_SIDE_MODE;
		//$form = new Form();
		$form->addHidden('id');
		$form->addText('name', 'Name')->setRequired();
		$form->addText('surname', 'Surname')->setRequired();
		$form->addEmail('email', 'E-mail')->setRequired();
		// $form->addMultiSelect('roles', 'Roles', $this->roleRepository->getIdNamePairs());
		$form->addCheckboxList('roles', 'Roles', $this->roleRepository->getIdNamePairs());
		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = [$this, 'userFormSuccess'];
		return $form;
	}

	public function userFormSuccess(Form $form, array $values): void
	{
		$userId = (int) $values['id'];
		unset($values['id']);

		if ($userId) {
			$user = $this->userRepository->findById($userId);

		// $user = $this->database->table(Tables::USERS)->get($userId);
		// 	$user->update($values);
		} else {
			$user = new User();
			//$this->database->table(Tables::USERS)->insert($values);
		}

		foreach ($values['roles'] as $roleId) {
			$roles[] = $this->roleRepository->findById($roleId);
		}
		unset($values['roles']);
		$user->setRoles($roles);
		$user->setValues($values);

		if ($this->userRepository->save($user)) {
			$this->flashMessage('User saved.');
		} else {
			$this->error('saving failed!');
		}
	}
}
