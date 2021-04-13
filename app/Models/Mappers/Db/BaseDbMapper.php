<?php
declare(strict_types=1);

namespace App\Models\Mappers\Db;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use dibi;
use Dibi\Connection;
use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use Nette\Caching\Cache;
use Nette\InvalidStateException;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

abstract class BaseDbMapper
{
	protected Connection $dibi;
	protected Cache $cache;
	protected string $table;

	protected const MAP = [];
	protected const DATA_TYPES = [];

	public function setDibi(Connection $dibi): void
	{
		$this->dibi = $dibi;
	}

	public function setCache(\Nette\Caching\Cache $cache): void
	{
		$this->cache = $cache;
	}

	public function init(): void
	{
		if (empty($this->table)) {
			throw new InvalidStateException('No database table defined for ' . get_called_class() . '!');
		}
	}

	/**
	 * @throws SavingErrorException
	 */
	protected function saveWithId(IdentifiedById $entity): bool
	{
		$data = [];
		foreach (static::MAP as $property => $key) {
			if (isset($entity->$property)) {
				$propertyValue = $entity->$property;
				if ($propertyValue instanceof IdentifiedById) {
					$propertyValue = $propertyValue->getId();
				}
				$data[$key] = $propertyValue;
			}
		}
		return $this->saveData($data, $entity);
	}

	/**
	 * @throws SavingErrorException
	 */
	protected function saveData(array $data, IdentifiedById $entity): bool
	{
		try {
			unset($data['id']);
			$id = $entity->getId();
			if ($id === null) {
				$id = $this->dibi->insert($this->table, $data)->execute(dibi::IDENTIFIER);
				$entity->setId($id);
				return true;
			}
			$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
			return true;
		} catch (\Dibi\Exception $e) {
			throw new SavingErrorException('Saving failed!');
		}
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Entity
	{
		$data = $this->getResult($filter)->fetch();
		if (empty($data)) {
			throw new EntityNotFoundException('Requested entity was not found!');
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
