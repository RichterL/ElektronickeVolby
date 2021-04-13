<?php
declare(strict_types=1);

namespace App\Forms;

use App\Core\Utils\Form\BootstrapForm;
use App\Core\Utils\Form\BootstrapRenderer;

final class FormFactory
{
	public function getForm(bool $ajax = true): BootstrapForm
	{
		$form = new BootstrapForm();
		$form->setAjax($ajax);
		$form->setRenderer(new BootstrapRenderer());
		return $form;
	}
}
