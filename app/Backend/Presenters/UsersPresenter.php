<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use App\Models\Entities\User;
use Nette\Application\UI\Form;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Nette\Security\Passwords;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use App\Backend\Utils\DataGrid\Action;
use App\Backend\Utils\DataGrid\Column;
use App\Backend\Utils\DataGrid\ToolbarButton;

final class UsersPresenter extends BasePresenter
{
	private UserRepository $userRepository;
	private RoleRepository $roleRepository;

	public function __construct(
		UserRepository $userRepository,
		RoleRepository $roleRepository
	) {
		parent::__construct();
		$this->userRepository = $userRepository;
		$this->roleRepository = $roleRepository;
	}

	/**
	 * @restricted
	 * @resource(users)
	 * @privilege(view)
	 */
	public function actionDefault(): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

	public function renderDefault(): void
	{
		$users = $this->userRepository->findAll();
		$this->template->users = $users;
	}

	/**
	 * @restricted
	 * @resource(users)
	 * @privilege(edit)
	 */
	public function handleEdit(int $id): void
	{
		try {
			$this->template->showUserForm = true;
			$user = $this->userRepository->findById($id);
			$this['userForm']->setDefaults($user->toArray());
			$this->template->userEdit = true;
			$this->redrawControl('userFormSnippet');
		} catch (EntityNotFoundException $e) {
			$this->error('User not found');
		}
	}

	/**
	 * @restricted
	 * @resource(users)
	 * @privilege(delete)
	 */
	public function handleDelete(int $id): void
	{
		try {
			$user = $this->userRepository->findById($id);
			$this->userRepository->delete($user);
			$this->flashMessage('User id ' . $id . ' deleted', 'success');
			$this->getGrid('usersGrid')->reload();
		} catch (EntityNotFoundException $e) {
			$this->flashMessage('User was not found!', 'error');
		} catch (DeletingErrorException $e) {
			$this->flashMessage('deleting the user failed!', 'error');
		}
	}

	/**
	 * @restricted
	 * @resource(users)
	 * @privilege(edit)
	 */
	public function handleShowUserForm(): void
	{
		$this->template->showUserForm = true;
		$this->redrawControl('userFormSnippet');
	}

	public function handleHideUserForm(): void
	{
		$this->template->showUserForm = false;
		$this->redrawControl('userFormSnippet');
	}

	public function createComponentUsersGrid(string $name): void
	{
		$roles = $this->roleRepository->getIdNamePairs();
		$grid = $this->addGrid('usersGrid', $this->userRepository->getDataSource(), 'users')
			->addColumn(Column::FILTERTEXT, 'username', 'Username')
			->addColumn(Column::FILTERTEXT, 'email', 'E-mail')
			->addColumn(Column::TEXT, 'name', 'Name')
			->addColumn(Column::FILTERTEXT, 'surname', 'Surname')
			->addColumn(Column::TEXT, 'roles', 'Roles')
			->addAction(Action::EDIT, 'edit!');

			$grid->addConfirmAction(
				Action::DELETE,
				new StringConfirmation('Do you really want to delete user %s', 'username'),
				'delete!'
			);

			$grid->addToolbarButton(
				ToolbarButton::ADD,
				'Add new user',
				'showUserForm!'
			);

		$this->getGrid('usersGrid')->addFilterMultiSelect('roles', 'roles', $roles)
			->setCondition(function (\Dibi\Fluent $fluent, $value) {
				$fluent->where('ar.id IN (%s)', $value);
			});
	}

	public function createComponentUserForm(): Form
	{
		$form = new BootstrapForm();
		$form->setAjax();
		$form->getElementPrototype()->addAttributes(['novalidate' => true]);
		$form->renderMode = RenderMode::SIDE_BY_SIDE_MODE;
		$form->addHidden('id');
		$form->addText('name', 'Name')->setRequired();
		$form->addText('surname', 'Surname')->setRequired();
		$form->addCheckbox('resetPassword', 'Reset password');
		$form->addPassword('password', 'Password')
			->addConditionOn($form['resetPassword'], $form::EQUAL, true)
				->addRule($form::FILLED, 'Password is required')
			->endCondition()
				->addConditionOn($form['id'], $form::BLANK)
				->addRule($form::FILLED, 'Password is required for new user');
		$form->addPassword('passwordCheck', 'Confirm password')
			->addConditionOn($form['password'], $form::FILLED)
				->addRule($form::FILLED, 'Password check is required')
				->addRule($form::EQUAL, 'Passwords must match', $form['password']);

		$form->addEmail('email', 'E-mail')->setRequired();
		$form->addCheckboxList('roles', 'Roles', $this->roleRepository->getIdNamePairs());
		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = [$this, 'userFormSuccess'];
		$form->onError[] = function () {
			$this->flashMessage('form error', 'warning');
			$this->handleShowUserForm();
		};
		return $form;
	}

	public function userFormSuccess(Form $form, array $values): void
	{
		try {
			$userId = (int) $values['id'];
			unset($values['id']);
			$user = empty($userId) ? new User() : $this->userRepository->findById($userId);
			$roles = [];
			foreach ($values['roles'] as $roleId) {
				$roles[] = $this->roleRepository->findById($roleId);
			}
			unset($values['roles']);
			$user->setRoles(...$roles);
			$user->setValues($values);
			if (!empty($values['password'])) {
				$passwords = new Passwords();
				$user->setPassword($passwords->hash($values['password']));
			}
			$this->userRepository->save($user);
			$this->flashMessage('User saved.');
			if ($this->isAjax()) {
				$this->redrawControl('userFormSnippet');
				$grid = $this->getGrid('usersGrid');
				empty($userId) ? $grid->reload() : $grid->redrawItem($userId, 'u.id');
			} else {
				$this->redirect('this');
			}
		} catch (EntityNotFoundException | SavingErrorException $e) {
			$this->flashMessage('saving failed', 'error');
		}
	}
}
