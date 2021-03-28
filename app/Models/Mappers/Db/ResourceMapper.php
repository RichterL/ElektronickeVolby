<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use dibi;
use Exception;
use Models\Entities\Resource\Resource;
use Models\Mappers\IResourceMapper;

class ResourceMapper extends BaseMapper implements IResourceMapper
{
	const MAP = [
		'id' => 'id',
		'name' => 'name',
		'key' => 'key',
		'parent' => 'parent',
	];

	protected $table = Tables::ACL_RESOURCES;
	private PrivilegeMapper $privilegeMapper;

	public function __construct(PrivilegeMapper $privilegeMapper)
	{
		$this->privilegeMapper = $privilegeMapper;
	}

	public function create(array $data = []): Resource
	{
		$resource = new Resource();
		if (!empty($data)) {
			$resource->setId($data['id']);
			$resource->key = $data['key'];
			$resource->name = $data['name'];
			if (!empty($data['parent'])) {
				$resource->parent = $this->findOne(['id' => $data['parent']]);
			}
		}
		$resource->privileges = $this->privilegeMapper->findRelated($resource);
		return $resource;
	}

	public function save(Resource $resource): bool
	{
		$data = [];
		foreach (self::MAP as $property => $key) {
			if (isset($resource->$property)) {
				$propertyValue = $resource->$property;
				if ($propertyValue instanceof Resource) {
					$propertyValue = $propertyValue->getId();
				}
				$data[$key] = $propertyValue;
			}
		}
		unset($data['id']);
		$id = $resource->getId();
		if (empty($id)) {
			$id = $this->dibi->insert($this->table, $resource->toArray())->execute(dibi::IDENTIFIER);
			if (!$id) {
				throw new Exception('insert failed');
			}
			$resource->setId($id);
			return true;
		}

		$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
		return true;
	}

	public function savePrivileges(Resource $resource): bool
	{
		$ret = false;
		foreach ($resource->privileges as $privilege) {
			$ret ?: $this->privilegeMapper->save($resource, $privilege);
		}
		return $ret;
	}

	/** parent concrete implementetions */
	public function findOne(array $filter = []): ?Resource
	{
		return parent::findOne($filter);
	}

	/** @var Resource[] */
	public function findAll(): array
	{
		return $this->cache->load('resource.findAll', function () {
			return parent::findAll();
		});
	}
}
