<?php
declare(strict_types=1);

namespace App\Models\Mappers\Db;

use App\Models\Mappers\Exception\EntityNotFoundException;
use dibi;
use Dibi\Row;
use Exception;
use App\Models\Entities\Role\Role;
use App\Models\Entities\User;
use App\Models\Mappers\IRoleMapper;

class RoleMapper extends BaseMapper implements IRoleMapper
{
	const MAP = [
		'id' => 'id',
		'name' => 'name',
		'key' => 'key',
		'parent' => 'parent',
	];

	protected string $table = Tables::ACL_ROLES;
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

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Role
	{
		return parent::findOne($filter);
	}

	/**
	 * @return Role[]
	 */
	public function findAll(): array
	{
		return $this->cache->load('role.findAll', function () {
			return parent::findAll();
		});
	}
}
