<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

use App\Core\Classes\LDAP\NoConnectionException;
use Contributte\FormsBootstrap\BootstrapForm;
use App\Core\Classes\LdapAuthenticator;
use Contributte\FormsBootstrap\BootstrapRenderer;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use App\Core\Classes\PasswordAuthenticator;

final class SignPresenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
	public string $backlink = '';

	private PasswordAuthenticator $passwordAuthenticator;
	private LdapAuthenticator $ldapAuthenticator;

	public function __construct(PasswordAuthenticator $passwordAuthenticator, LdapAuthenticator $ldapAuthenticator)
	{
		parent::__construct();
		$this->passwordAuthenticator = $passwordAuthenticator;
		$this->ldapAuthenticator = $ldapAuthenticator;
	}

	protected function createComponentSignInForm(): Form
	{
		$form = new BootstrapForm();
		$form->setRenderer(new BootstrapRenderer());
		$form->setHtmlAttribute('class', 'form-signin');
		$form->addText('username', 'Login')
			->setRequired('Login cannot be empty!')
			->setHtmlAttribute('placeholder', 'Login');
		$form->addPassword('password', 'Password')
			->setRequired('Password cannot be empty!')
			->setHtmlAttribute('placeholder', 'Password');
		$form->addSubmit('submit', 'Sign in')
			->setBtnClass('btn btn-lg btn-primary btn-block text-uppercase');
		$form->onSuccess[] = [$this, 'signInFormSuccess'];
		$form->onError[] = function() { $this->redrawControl(); };
		return $form;
	}

	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->flashMessage('Odhlaseni uspesne');
		$this->redirect('Homepage:');
	}

	public function signInFormSuccess(Form $form, \stdClass $values): void
	{
		$user = $this->getUser();
		try {
			$user->setAuthenticator($this->passwordAuthenticator);
			$user->login($values->username, $values->password);
			$this->restoreRequest($this->backlink);
			$this->redirect('Homepage:');
		} catch (AuthenticationException $ex) {
			try {
				$user->setAuthenticator($this->ldapAuthenticator);
				$user->login($values->username, $values->password);
				$this->redirect('Homepage:');
			} catch (AuthenticationException $ex) {
				$form->addError('Username or password invalid');
			} catch (NoConnectionException $ex) {
				$form->addError('LDAP server not available');
			}
		}
	}
}
