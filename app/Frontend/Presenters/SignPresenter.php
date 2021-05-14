<?php

declare(strict_types=1);

namespace App\Frontend\Presenters;

use App\Core\Classes\LDAP\NoConnectionException;
use Contributte\FormsBootstrap\BootstrapForm;
use App\Core\Classes\LdapAuthenticator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use App\Core\Classes\PasswordAuthenticator;

final class SignPresenter extends Nette\Application\UI\Presenter
{
	private $passwordAuthenticator;
	private $ldapAuthenticator;

	public function __construct(PasswordAuthenticator $passwordAuthenticator, LdapAuthenticator $ldapAuthenticator)
	{
		$this->passwordAuthenticator = $passwordAuthenticator;
		$this->ldapAuthenticator = $ldapAuthenticator;
	}

	protected function createComponentSignInForm(): Form
	{
		$form = new BootstrapForm();
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
		return $form;
	}

	public function actionOut()
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
