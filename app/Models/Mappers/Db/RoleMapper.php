<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use dibi;
use Dibi\Row;
use Exception;
use Models\Entities\Role\Role;
use Models\Entities\User;
use Models\Mappers\IRoleMapper;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

class RoleMapper extends BaseMapper implements IRoleMapper
{
	const MAP = [
		'id' => 'id',
		'name' => 'name',
		'key' => 'key',
		'parent' => 'parent',
	];

	protected $table = Tables::ACL_ROLES;
	private $usersRolesTable = Tables::USERS_ROLES;

	public function create(array $data = []): Role
	{
		$role = new Role();
		if (!empty($data)) {
			$role->setId($data['id']);
			$role->key = $data['key'];
			$role->name = $data['name'];
			//$role->setParent();
		}
		return $role;
	}

	/** @return Role[] */
	public function findRelated(User $user): array
	{
		$roles = [];
		$result = $this->dibi->select('*')->from('%n r', $this->table)
			->leftJoin('%n ur', $this->usersRolesTable)->on('r.id = ur.role_id')
			->where('ur.user_id = %i', $user->getId())
			->fetchAll();
		/** @var Row */
		foreach ($result as $role) {
			$roles[] = $this->create($role->toArray());
		}
		return $roles;
	}

	public function save(Role $role): bool
	{
		$data = [];
		foreach (self::MAP as $property => $key) {
			if (isset($role->$property)) {
				$data[$key] = $role->$property;
			}
		}
		unset($data['id']);
		$id = $role->getId();
		if (empty($id)) {
			$id = $this->dibi->insert($this->table, $role->toArray())->execute(dibi::IDENTIFIER);
			if (!$id) {
				throw new Exception('insert failed');
			}
			$role->setId($id);
			return true;
		}

		$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
		return true;
	}

	public function getDataSource(): DibiFluentDataSource
	{
		$fluent = $this->dibi->select('*')->from($this->table);
		return new DibiFluentDataSource($fluent, 'id');
	}

	/** parent concrete implementetions */
	public function findOne(array $filter = []): ?Role
	{
		return parent::findOne($filter);
	}

	/** @return Role[] */
	public function findAll(): array
	{
		return parent::findAll();
	}
}
