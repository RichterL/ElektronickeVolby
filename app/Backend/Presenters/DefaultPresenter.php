<?php

declare(strict_types=1);

namespace App\Backend\Presenters;

use App\Controls\Menu;
use InvalidArgumentException;
use Nette;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\IDataSource;

abstract class DefaultPresenter extends Nette\Application\UI\Presenter
{
	public function checkRequirements($element): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
		parent::checkRequirements($element);
	}

	public function createComponentMenu()
	{
		return new Menu();
	}

	protected function getForm(string $name): Form
	{
		return $this->getConcreteComponent(Form::class, $name);
	}

	public function addGrid(string $name, IDataSource $dataSource)
	{
		$grid = new \Utils\DataGrid\DataGrid($dataSource);
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