<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use dibi;
use Dibi\Connection;
use Dibi\Row;
use Models\Entities\Entity;
use Models\Entities\IdentifiedById;
use Nette\InvalidStateException;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

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
		$data = $this->getResult($filter)->fetch();
		if (empty($data)) {
			return null;
		}
		return $this->create($data->toArray());
	}

	public function find(array $filter = []): iterable
	{
		$collection = static::createCollection();
		$rows = $this->getResult($filter)->fetchAll();
		foreach ($rows as $row) {
			$collection[] = $this->create($row->toArray());
		}
		return $collection;
	}

	public function getResult(array $filter = []): Dibi\Result
	{
		static::applyMapToFilter($filter);
		$result = $this->dibi->select('*')->from($this->table)->where($filter)->execute();
		static::applyDataTypes($result);
		return $result;
	}

	/** @deprecated use find() */
	public function findAll(): iterable
	{
		return $this->find();
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

	public static function createCollection(): iterable
	{
		return [];
	}

	public function delete(IdentifiedById $entity): bool
	{
		return (bool) $this->dibi->delete($this->table)->where('id = %i', $entity->getId())->execute(dibi::AFFECTED_ROWS);
	}

	public function getDataSource(array $filter = []): DibiFluentDataSource
	{
		$fluent = $this->dibi->select('*')->from($this->table);
		if (!empty($filter)) {
			$fluent->where($filter);
		}
		return new DibiFluentDataSource($fluent, 'id');
	}

	abstract public function create(array $data = []): Entity;
}
