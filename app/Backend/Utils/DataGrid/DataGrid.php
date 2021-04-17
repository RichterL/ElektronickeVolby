<?php
declare(strict_types=1);

namespace App\Backend\Utils\DataGrid;

use App\Core\Utils\Constants;
use InvalidArgumentException;
use Nette\Security\User;
use Ublaboo\DataGrid\Column\Action\Confirmation\IConfirmation;
use Ublaboo\DataGrid\DataSource\IDataSource;

class DataGrid
{
	private \Ublaboo\DataGrid\DataGrid $grid;
	private ?User $user = null;
	private ?string $resource = null;

	public function __construct(IDataSource $dataSource, User $user = null, string $resource = null, string $primaryKey = 'id')
	{
		$this->grid = new \Ublaboo\DataGrid\DataGrid();
		$this->grid->setPrimaryKey($primaryKey);
		$this->grid->setDataSource($dataSource);
		$this->user = $user;
		$this->resource = $resource;
	}

	public function addAction(string $type, ?string $destination = null, ?array $params = null, bool $ajax = true, bool $keepHistory = false, bool $restricted = true): self
	{
		if (!Action::isValid($type)) {
			throw new InvalidArgumentException('Action ' . $type . ' is not defined');
		}
		if ($this->resource !== null && $restricted && !$this->user->isAllowed($this->resource, $type)) {
			return $this;
		}
		$action = $this->grid->addAction($type, '', $destination, $params);
		switch ($type) {
			case Action::VIEW:
				$action->setIcon('eye')
					->setTitle('View details');
				$class = 'btn btn-sm btn-info text-white';
				break;
			case Action::EDIT:
				$action->setIcon('edit')
					->setTitle('Edit');
				$class = 'btn btn-sm btn-warning text-white';
				break;
			case Action::DELETE:
				$action->setIcon('trash')
					->setTitle('Delete');
				$class = 'btn btn-sm btn-danger';
				break;
			case Action::DOWNLOAD:
				$action->setIcon('download')
					->setTitle('Download');
				$class = 'btn btn-sm btn-primary text-white';
				break;
			case Action::APPLY:
				$action->setIcon('check')
					->setTitle('Apply');
				$class = 'btn btn-sm btn-success';
				break;
			default:
				throw new InvalidArgumentException('Action ' . $type . ' is not defined');
		}
		$class .= $ajax ? ' ajax' : '';
		$action->setClass($class);
		if (!$keepHistory) {
			$action->setDataAttribute('naja-history', 'off');
		}
		return $this;
	}

	public function addConfirmAction(string $type, IConfirmation $confirm, ?string $destination = null, ?array $params = null, bool $restricted = true): self
	{
		if ($this->resource !== null && $restricted && !$this->user->isAllowed($this->resource, $type)) {
			return $this;
		}
		$this->addAction($type, $destination, $params);
		$this->grid->getAction($type)->setConfirmation($confirm);
		return $this;
	}

	public function addColumn(string $type, string $key, string $title = '', array $items = []): self
	{
		switch ($type) {
			case Column::NUMBER:
				$this->grid->addColumnNumber($key, $title)->setSortable();
				break;
			case Column::TEXT:
				$this->grid->addColumnText($key, $title)->setSortable()
					->setReplacement($items);
				break;
			case Column::FILTERTEXT:
				$this->grid->addColumnText($key, $title)->setSortable()
					->setReplacement($items)
					->setFilterText();
				break;
			case Column::TEXT_MULTISELECT:
				$this->grid->addColumnText($key, $title)->setSortable()
					->setReplacement($items)
					->setFilterMultiSelect($items);
				break;
			case Column::BOOL:
				$this->grid->addColumnText($key, $title)
					->setReplacement(['no', 'yes'])
					->setFilterMultiSelect(['no', 'yes']);
				break;
			case Column::DATETIME:
				$this->grid->addColumnDateTime($key, $title)
					->setSortable()
					->setFormat(Constants::DATETIME_FORMAT)
					->setFilterDateRange();
				break;
			default:
				throw new InvalidArgumentException('Column ' . $type . ' is not defined');
		}

		return $this;
	}

	public function getOriginal(): \Ublaboo\DataGrid\DataGrid
	{
		return $this->grid;
	}

	public function addToolbarButton(string $type, string $title = '', string $destination = '', bool $restricted = true): self
	{
		if ($this->resource !== null && $restricted && !$this->user->isAllowed($this->resource, $type)) {
			return $this;
		}
		switch ($type) {
			case ToolbarButton::ADD:
				$this->grid->addToolbarButton($destination, $title)
					->setIcon('plus')
					->setTitle($title)
					->setClass('btn btn-sm btn-primary ajax');
				break;

			default:
				throw new InvalidArgumentException('Toolbar button ' . $type . ' is not defined');
		}
		return $this;
	}
}
