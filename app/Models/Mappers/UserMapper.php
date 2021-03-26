<?php

declare(strict_types=1);

namespace Models\Mappers;

use dibi;
use Dibi\Connection;
use ErrorException;
use Exception;
use Models\Entities\Role\Role;
use Models\Entities\User;
use Models\Mappers\Db\Tables;

class UserMapper extends BaseMapper
{
	const MAP = [
		'id' => 'id',
		'username' => 'username',
		'password' => 'password',
		'name' => 'name',
		'surname' => 'surname',
		'email' => 'email',
	];

	protected $table = Tables::USERS;
	protected $userRolesTable = Tables::USERS_ROLES;
	private $roleMapper;

	public function __construct(Connection $dibi, RoleMapper $roleMapper)
	{
		parent::__construct($dibi);
		$this->roleMapper = $roleMapper;
	}

	public function create(array $data = []): User
	{
		$user = new User();
		if (!empty($data)) {
			foreach (self::MAP as $property => $key) {
				if (!empty($data[$key])) {
					$user->$property = $data[$key];
				}
			}
		}
		$user->setRoles($this->roleMapper->findRelated($user));
		return $user;
	}

	public function saveData(User $user): bool
	{
		$data = [];
		foreach (self::MAP as $property => $key) {
			$data[$key] = $user->$property;
		}
		$data = array_filter($data);
		unset($data['id']); // necessary?
		$id = $user->getId();
		if (empty($id)) {
			$id = $this->dibi->insert($this->table, $data)->execute(dibi::IDENTIFIER);
			if (!$id) {
				throw new Exception('insert failed');
			}
			$user->setId($id);
			return true;
		}

		$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
		return true;
	}

	public function save(User $user): bool
	{
		try {
			$this->dibi->begin();
			$this->saveData($user);
			foreach ($user->getRoles() as $role) {
				$data[] = [
					'user_id' => $user->getId(),
					'role_id' => $role->getId(),
				];
			}
			$this->dibi->delete($this->userRolesTable)->where('user_id = %i', $user->getId())->execute();
			foreach ($data as $item) {
				$this->dibi->insert($this->userRolesTable, $item)->execute();
			}
			$this->dibi->commit();
		} catch (\Throwable $th) {
			throw $th;
			return false;
		}
		return true;
	}

	public function getDataSource()
	{
		return $this->dibi->select('u.`id`, u.`username`, u.`email`, u.`name`, u.`surname`, u.`full_name`, GROUP_CONCAT(ar.`key`) AS `roles`')
			->from('%n u', Tables::USERS)
			->leftJoin('%n ur', Tables::USERS_ROLES)->on('ur.user_id = u.id')
			->leftJoin('%n ar', Tables::ACL_ROLES)->on('ur.role_id = ar.id')
			->groupBy('u.`id`');
	}
}
