<?php
declare(strict_types=1);

namespace Utils\DataGrid;

use Constants;
use InvalidArgumentException;
use Ublaboo\DataGrid\Column\Action\Confirmation\IConfirmation;
use Ublaboo\DataGrid\DataSource\IDataSource;

class DataGrid
{
	private \Ublaboo\DataGrid\DataGrid $grid;

	public function __construct(IDataSource $dataSource)
	{
		$this->grid = new \Ublaboo\DataGrid\DataGrid();
		$this->grid->setDataSource($dataSource);
	}

	public function addAction(string $type, ?string $destination = null, ?array $params = null, bool $ajax = true): self
	{
		if (!Action::isValid($type)) {
			throw new InvalidArgumentException('Action ' . $type . ' is not defined');
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
			default:
				throw new InvalidArgumentException('Action ' . $type . ' is not defined');
		}
		$class .= $ajax ? ' ajax' : '';
		$action->setClass($class);
		return $this;
	}

	public function addConfirmAction(string $type, IConfirmation $confirm, ?string $destination = null, ?array $params = null)
	{
		$this->addAction($type, $destination, $params);
		$this->grid->getAction($type)->setConfirmation($confirm);
		return $this;
	}

	public function addColumn(string $type, string $key, string $title = '', array $items = []): self
	{
		switch ($type) {
			case Column::NUMBER:
				$this->grid->addColumnNumber($key, $title);
				break;
			case Column::TEXT:
				$this->grid->addColumnText($key, $title)
					->setReplacement($items);
				break;
			case Column::TEXT_MULTISELECT:
				$this->grid->addColumnText($key, $title)
					->setReplacement($items)
					->setFilterMultiSelect($items);
				break;
			case Column::BOOL:
				$this->grid->addColumnText($key, $title)
					->setReplacement(['no', 'yes'])
					->setFilterMultiSelect(['no', 'yes']);
				break;
			case Column::DATETIME:
				$this->grid->addColumnDateTime($key, $title)->setFormat(Constants::DATETIME_FORMAT)->setFilterDateRange();
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

	public function addToolbarButton(string $type, string $title = '', string $destination = '')
	{
		switch ($type) {
			case ToolbarButton::ADD:
				$this->grid->addToolbarButton($destination, '')
					->setIcon('plus')
					->setTitle($title)
					->setClass('btn btn-sm btn-primary ajax');
				break;

			default:
				throw new InvalidArgumentException('Toolbar button ' . $type . ' is not defined');
		}
	}
}
