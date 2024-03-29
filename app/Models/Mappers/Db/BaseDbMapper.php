<?php
declare(strict_types=1);

namespace App\Models\Mappers\Db;

use App\Models\Entities\Identifier;
use App\Models\Mappers\Exception\DeletingErrorException;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use dibi;
use Dibi\Connection;
use Nette\InvalidStateException;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

abstract class BaseDbMapper
{
	protected Connection $dibi;
	protected string $table;

	protected const MAP = [];
	protected const DATA_TYPES = [];

	public function setDibi(Connection $dibi): void
	{
		$this->dibi = $dibi;
	}

	public function init(): void
	{
		if (empty($this->table)) {
			throw new InvalidStateException('No database table defined for ' . static::class . '!');
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
				if ($propertyValue instanceof Identifier) {
					$propertyValue = $propertyValue->getValue();
				}
				if ($propertyValue instanceof \JsonSerializable) {
					$propertyValue = json_encode($propertyValue);
				}
				$data[$key] = $propertyValue;
			}
		}
		return $this->saveData($data, $entity);
	}

	/**
	 * @param array<string, mixed> $data
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
	 * @param array<string, mixed> $filter
	 */
	public function findOne(array $filter = []): Entity
	{
		$data = $this->getResult($filter)->fetch();
		if (empty($data)) {
			throw new EntityNotFoundException('Requested entity was not found!');
		}
		return $this->create($data->toArray());
	}

	/**
	 * @return iterable<int, Entity>
	 * @param array<string, mixed> $filter
	 */
	public function find(array $filter = []): iterable
	{
		$collection = static::createCollection();
		$rows = $this->getResult($filter)->fetchAll();
		foreach ($rows as $row) {
			$collection[] = $this->create($row->toArray());
		}
		return $collection;
	}

	/**
	 * @param array<string, mixed> $filter
	 */
	public function getResult(array $filter = []): \Dibi\Result
	{
		static::applyMapToFilter($filter);
		$result = $this->dibi->select('*')->from($this->table)->where($filter)->execute();
		static::applyDataTypes($result);
		return $result;
	}

	/**
	 * @deprecated use find()
	 * @return iterable<int, Entity>
	 */
	public function findAll(): iterable
	{
		return $this->find();
	}

	/**
	 * @param array<string, mixed>|null $filter
	 */
	public static function applyMapToFilter(?array &$filter = []): void
	{
		if (empty($filter)) {
			return;
		}

		foreach ($filter as $property => $value) {
			if ($value instanceof IdentifiedById) {
				$value = $value->getId();
			}
			if (static::MAP[$property] !== $property) {
				unset($filter[$property]);
			}
			$filter[static::MAP[$property]] = $value;
		}
	}

	public static function applyDataTypes(\Dibi\Result $result): void
	{
		foreach (static::DATA_TYPES as $key => $type) {
			$result->setType($key, $type);
		}
	}

	/**
	 * @return iterable<int, Entity>
	 */
	public static function createCollection(): iterable
	{
		return [];
	}

	/**
	 * @throws DeletingErrorException
	 */
	public function delete(IdentifiedById $entity): bool
	{
		try {
			return (bool)$this->dibi->delete($this->table)->where('id = %i', $entity->getId())->execute(dibi::AFFECTED_ROWS);
		} catch (\Dibi\Exception $e) {
			throw new DeletingErrorException($e->getMessage());
		}
	}

	/**
	 * @param array<string, mixed> $filter
	 */
	public function getDataSource(array $filter = []): DibiFluentDataSource
	{
		$fluent = $this->dibi->select('*')->from($this->table);
		if (!empty($filter)) {
			$fluent->where($filter);
		}
		return new DibiFluentDataSource($fluent, 'id');
	}

	public function beginTransaction(): void
	{
		$this->dibi->begin();
	}

	public function finishTransaction(): void
	{
		$this->dibi->commit();
	}

	public function rollbackTransaction(): void
	{
		$this->dibi->rollback();
	}

//	/**
//	 * @param array<string, mixed> $data
//	 */
//	abstract public function create(array $data = []): Entity;
}
