<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use App\Models\Entities\User;
use Nette\Application\UI\Form;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Utils\DataGrid\Action;
use Utils\DataGrid\Column;
use Utils\DataGrid\ToolbarButton;

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

	public function handleEdit(int $id)
	{
		$this->template->showUserForm = true;
		$user = $this->userRepository->findById($id);
		if (!$user) {
			$this->error('User not found');
		}
		$this['userForm']->setValues($user->toArray());
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
		$roles = $this->roleRepository->getIdNamePairs();
		$this->addGrid('usersGrid', $this->userRepository->getDataSource())
			->addColumn(Column::FILTERTEXT, 'username', 'Username')
			->addColumn(Column::FILTERTEXT, 'email', 'E-mail')
			->addColumn(Column::TEXT, 'name', 'Name')
			->addColumn(Column::FILTERTEXT, 'surname', 'Surname')
			->addColumn(Column::TEXT, 'roles', 'Roles')
			->addAction(Action::EDIT, 'edit!')
			->addConfirmAction(Action::DELETE, new StringConfirmation('Do you really want to delete user %s', 'username'), 'delete!')
			->addToolbarButton(ToolbarButton::ADD, 'Add new user', 'showUserForm!');
		$this->getGrid('usersGrid')->addFilterMultiSelect('roles', 'roles', $roles)
			->setCondition(function (\Dibi\Fluent $fluent, $value) {
				$fluent->where('ar.id IN (%s)', $value);
			});
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
		$user->setRoles(...$roles);
		$user->setValues($values);

		if ($this->userRepository->save($user)) {
			$this->flashMessage('User saved.');
		} else {
			$this->error('saving failed!');
		}
	}

	public function handleDelete(int $id)
	{
		$user = $this->userRepository->findById($id);
		if (!$user) {
			$this->flashMessage('User wasn\'t found!', 'error');
			return;
		}
		if ($this->userRepository->delete($user)) {
			$this->flashMessage('User id ' . $id . ' deleted', 'success');
			$this->getGrid('usersGrid')->reload();
		}
	}
}
