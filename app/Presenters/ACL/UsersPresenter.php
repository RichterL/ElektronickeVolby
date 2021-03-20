<?php

declare(strict_types=1);

namespace App\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Models\AclModel;
use Models\Entities\User;
use Models\Tables;
use Nette;
use Nette\Application\UI\Form;
use Repositories\RoleRepository;
use Repositories\UserRepository;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\NetteDatabaseDataSource\NetteDatabaseDataSource;

final class UsersPresenter extends Nette\Application\UI\Presenter
{
	private Nette\Database\Explorer $database;
	private AclModel $aclModel;
	private UserRepository $userRepository;
	private RoleRepository $roleRepository;

	public function __construct(
		Nette\Database\Explorer $database,
		AclModel $aclModel,
		UserRepository $userRepository,
		RoleRepository $roleRepository
	) {
		$this->aclModel = $aclModel;
		$this->database = $database;
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
		$datasource = $this->aclModel->getUsersDatasource();

		$grid->setDataSource($datasource);
		$grid->addColumnText('username', 'Username');
		$grid->addColumnText('email', 'E-mail');
		$grid->addColumnText('name', 'Name');
		$grid->addColumnText('surname', 'Surname');
		$grid->addColumnText('role', 'Role');
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
