<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

use App\Backend\Classes\Menu\Menu;
use InvalidArgumentException;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\IDataSource;

abstract class DefaultPresenter extends Nette\Application\UI\Presenter
{
	public function checkRequirements($element): void
	{
		$user = $this->getUser();
		parent::checkRequirements($element);
		if (!$user->isLoggedIn()) {
			if ($this->isAjax()) {
				$this->payload->forceRedirect = true;
			}
			$this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
		}
		if ($element instanceof Nette\Application\UI\MethodReflection && $element->hasAnnotation('restricted')) {
			$resource = $element->getAnnotation('resource');
			$privilege = $element->getAnnotation('privilege');
			if (!$user->isAllowed($resource, $privilege)) {
				if (!$user->isAllowed($resource, 'view')) {
					throw new Nette\Application\ForbiddenRequestException('You are not allowed to do that', 403);
				}
				$this->flashMessage('You do not have permission to do that', 'warning');
				$this->forward('this');
			}
		}
	}

	public function beforeRender()
	{
		Debugger::barDump($this->template->flashes ?? null);
		Debugger::barDump($this->template->showModal ?? null);
		Debugger::barDump($this->payload ?? null);
		if ($this->isAjax()) {
			$this->redrawControl('flashes');
			if ((bool) $this->getParameter('isModal')) {
				$this->payload->showModal = true;
				$this->payload->modalId = 'myModal';
				$this->redrawControl('modal');
			}
		}
	}

	public function createComponentMenu()
	{
		return new Menu();
	}

	protected function getForm(string $name): Form
	{
		return $this->getConcreteComponent(Form::class, $name);
	}

	public function addGrid(string $name, IDataSource $dataSource, ?string $resource = null, string $primaryKey = 'id'): \App\Backend\Utils\DataGrid\DataGrid
	{
		$grid = new \App\Backend\Utils\DataGrid\DataGrid($dataSource, $this->getUser(), $resource, $primaryKey);
		$this->addComponent($grid->getOriginal(), $name);
		return $grid;
	}

	public function getGrid(string $name): DataGrid
	{
		return $this->getConcreteComponent(DataGrid::class, $name);
	}

	private function getConcreteComponent(string $class, string $name): \Nette\ComponentModel\IComponent
	{
		$component = $this->getComponent($name);
		if ($component instanceof $class) {
			return $component;
		}
		throw new InvalidArgumentException('Component "' . $name . '" is not of class ' . $class);
	}

	protected function getLoggedUserId()
	{
		return $this->user->getIdentity()->getId();
	}

	public function flashMessage($message, string $type = 'info'): \stdClass
	{
		if ($this->isAjax()) {
			$this->redrawControl('flashes');
		}
		return parent::flashMessage($message, $type);
	}
}
