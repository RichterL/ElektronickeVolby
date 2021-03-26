<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use dibi;
use Exception;
use Models\Entities\Resource\Privilege;
use Models\Entities\Resource\PrivilegeCollection;
use Models\Entities\Resource\Resource;
use Models\Mappers\IPrivilegeMapper;

class PrivilegeMapper extends BaseMapper implements IPrivilegeMapper
{
	const MAP = [
		'id' => 'id',
		'name' => 'name',
		'key' => 'key',
	];

	protected $table = Tables::ACL_RESOURCE_PRIVILEGE;

	public function create(array $data = []): Privilege
	{
		$privilege = new Privilege();
		if (!empty($data)) {
			$privilege->setId($data['id']);
			$privilege->key = $data['key'];
			$privilege->name = $data['name'];
		}
		return $privilege;
	}

	public function save(Resource $resource, Privilege $privilege): bool
	{
		$data = [];
		foreach (self::MAP as $property => $key) {
			if (isset($privilege->$property)) {
				$data[$key] = $privilege->$property;
			}
		}
		$data['resource_id'] = $resource->getId();
		unset($data['id']);
		$id = $privilege->getId();
		if (empty($id)) {
			$id = $this->dibi->insert($this->table, $data)->execute(dibi::IDENTIFIER);
			if (!$id) {
				throw new Exception('insert failed');
			}
			$privilege->setId($id);
			return true;
		}

		$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
		return true;
	}

	/** @var Privilege[] */
	public function findRelated(Resource $resource): PrivilegeCollection
	{
		$privileges = new PrivilegeCollection();
		$result = $this->dibi->select(array_keys(self::MAP))
			->from($this->table)
			->where('resource_id = %i', $resource->getId())
			->fetchAssoc('id,=');
		foreach ($result as $id => $values) {
			$privileges[] = $this->create($values)->setId($id);
		}
		return $privileges;
	}

	/** parent concrete implementetions */
	public function findOne(array $filter = []): ?Privilege
	{
		return parent::findOne($filter);
	}

	/** @return Privilege[] */
	public function findAll(): array
	{
		return parent::findAll();
	}
}
