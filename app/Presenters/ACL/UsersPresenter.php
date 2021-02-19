<?php

declare(strict_types=1);

namespace App\Presenters;

use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Models\Tables;
use Nette;
use Nette\Application\UI\Form;

final class UsersPresenter extends Nette\Application\UI\Presenter
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

	public function handleEdit(int $userId)
	{
		$this->template->showUserForm = true;
		$user = $this->database->table(Tables::USERS)->get($userId);
		if (!$user) {
			$this->error('User not found');
		}
		$this['userForm']->setDefaults($user->toArray());
		$this->redrawControl('userFormSnippet');
	}

	public function renderDefault(): void
	{
		$users = $this->database->table(Tables::USERS)->fetchAll();
		$this->template->users = $users;
	}

	public function handleShowUserForm()
	{
		$this->template->showUserForm = true;
		$this->redrawControl('userFormSnippet');
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
		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = [$this, 'userFormSuccess'];
		return $form;
	}

	public function userFormSuccess(Form $form, array $values): void
	{
		$userId = $values['id'];

		if ($userId) {
			$user = $this->database->table(Tables::USERS)->get($userId);
			$user->update($values);
		} else {
			unset($values['id']);
			$this->database->table(Tables::USERS)->insert($values);
		}

	}
}

