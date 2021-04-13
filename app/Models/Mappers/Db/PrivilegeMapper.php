<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use dibi;
use Exception;
use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use App\Models\Entities\Resource\Privilege;
use App\Models\Entities\Resource\PrivilegeCollection;
use App\Models\Entities\Resource\Resource;
use App\Models\Mappers\IPrivilegeMapper;

class PrivilegeMapper extends BaseMapper implements IPrivilegeMapper
{
	const MAP = [
		'id' => 'id',
		'name' => 'name',
		'key' => 'key',
		'resource' => 'resource_id',
	];

	protected string $table = Tables::ACL_RESOURCE_PRIVILEGE;

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
		return $this->saveData($data);
	}

	/** @var Privilege[] */
	public function findRelated(Resource $resource): PrivilegeCollection
	{
		$privileges = new PrivilegeCollection();
		$result = $this->dibi->select(array_values(self::MAP))
			->from($this->table)
			->where('resource_id = %i', $resource->getId())
			->fetchAssoc('id,=');
		foreach ($result as $id => $values) {
			$privileges[] = $this->create($values)->setId($id);
		}
		return $privileges;
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Privilege
	{
		return parent::findOne($filter);
	}

	public function findAll(): PrivilegeCollection
	{
		return $this->cache->load('privilege.findAll', function () {
			$privileges = new PrivilegeCollection();
			$result = $this->dibi->select(array_values(self::MAP))
				->from($this->table)
				->fetchAssoc('id,=');
			foreach ($result as $id => $values) {
				$privileges[] = $this->create($values)->setId($id);
			}
			return $privileges;
		});
	}
}
