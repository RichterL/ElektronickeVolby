<?php

namespace App\Forms;

final class FormFactory
{
	public function getForm(bool $ajax = true)
	{
		$form = new \App\Utils\Form\BootstrapForm();
		$form->setAjax($ajax);
		$form->setRenderer(new \App\Utils\Form\BootstrapRenderer());
		return $form;
	}
}
