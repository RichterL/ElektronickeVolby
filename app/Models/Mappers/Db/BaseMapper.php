<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use Dibi\Connection;
use Dibi\Row;
use Models\Entities\Entity;
use Nette\InvalidStateException;

abstract class BaseMapper
{
	protected $dibi;
	protected $table;

	public function __construct(Connection $dibi)
	{
		if (empty($this->table)) {
			throw new InvalidStateException('No database table defined for ' . get_called_class() . '!');
		}
		$this->dibi = $dibi;
	}

	//abstract public function create(array $data = []): DbEntity;

	// public function loadFromState(DbEntity $obj, array $data): DbEntity
	// {
	//     $rc = new ReflectionClass($obj);
	//     foreach ($rc->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
	//         $obj->{$property->getName()} = $data[StringHelper::camelToUnderscore($property->getName())];
	//     }
	//     return $obj;
	// }

	public function findOne(array $filter = []): ?Entity
	{
		$data = $this->dibi->select('*')->from($this->table)->where($filter)->fetch();
		if (empty($data)) {
			return null;
		}
		return $this->create($data->toArray());
	}

	public function findAll(): array
	{
		$all = [];
		$rows = $this->dibi->select('*')->from($this->table)->fetchAll();
		/** @var Row */
		foreach ($rows as $row) {
			$all[] = $this->create($row->toArray());
		}
		return $all;
	}

	abstract public function create(array $data = []): Entity;
}
