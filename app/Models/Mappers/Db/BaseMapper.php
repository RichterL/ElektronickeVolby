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
	protected $cache;
	protected $table;

	protected const MAP = [];
	protected const DATA_TYPES = [];

	public function setDibi(Connection $dibi)
	{
		$this->dibi = $dibi;
	}

	public function setCache(\Nette\Caching\Cache $cache)
	{
		$this->cache = $cache;
	}

	public function init()
	{
		if (empty($this->table)) {
			throw new InvalidStateException('No database table defined for ' . get_called_class() . '!');
		}
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
		static::applyMapToFilter($filter);
		$result = $this->dibi->select('*')->from($this->table)->where($filter)->execute();
		static::applyDataTypes($result);
		$data = $result->fetch();
		if (empty($data)) {
			return null;
		}
		return $this->create($data->toArray());
	}

	public function findAll(): array
	{
		$all = [];
		$result = $this->dibi->select('*')->from($this->table)->execute();
		static::applyDataTypes($result);
		$rows = $result->fetchAll();
		/** @var Row */
		foreach ($rows as $row) {
			$all[] = $this->create($row->toArray());
		}
		return $all;
	}

	public static function applyMapToFilter(?array &$filter = []): void
	{
		if (empty($filter)) {
			return;
		}

		foreach ($filter as $property => $value) {
			if (static::MAP[$property] == $property) {
				continue;
			}
			$filter[static::MAP[$property]] = $value;
			unset($filter[$property]);
		}
	}

	public static function applyDataTypes(\Dibi\Result $result): void
	{
		foreach (static::DATA_TYPES as $key => $type) {
			$result->setType($key, $type);
		}
	}

	abstract public function create(array $data = []): Entity;
}
