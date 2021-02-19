<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;

final class SignPresenter extends Nette\Application\UI\Presenter
{
    protected function createComponentSignInForm(): Form
    {
        $form = new Form();
        $form->addText('username', 'Login:')
            ->setRequired('Toto pole je povinne');
        $form->addPassword('password', 'Heslo:')
            ->setRequired('Heslo je nutne vyplnit!');
        $form->addSubmit('submit', 'Prihlasit');
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
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:');
        } catch (AuthenticationException $ex) {
            $form->addError($ex->getMessage());
        }
    }
}
