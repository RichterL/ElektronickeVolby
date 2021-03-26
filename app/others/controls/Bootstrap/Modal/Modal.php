<?php

namespace Bootstrap\Modal;

use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class Modal extends Control
{
	public function render()
	{
		$this->template->setFile(APP_DIR . '/others/controls/templates/modal.latte');
		$this->template->render();
	}

	public function addForm(): Form
	{
		return $this->getComponent('modalForm');
	}

	public function createComponentModalForm()
	{
		$form = new BootstrapForm();
		$this->addComponent($form, 'modalForm');
	}
}
