<?php

declare(strict_types=1);

namespace Models\Mappers;

use dibi;
use Dibi\Connection;
use ErrorException;
use Exception;
use Models\Entities\Role\Role;
use Models\Entities\User;
use Models\Tables;

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
			if (isset($user->$property)) {
				$data[$key] = $user->$property;
			}
		}
		unset($data['id']);
		$id = $user->getId();
		if (empty($id)) {
			$id = $this->dibi->insert($this->table, $user->toArray())->execute(dibi::IDENTIFIER);
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
		foreach ($user->getRoles() as $role) {
			$data[] = [
				'user_id' => $user->getId(),
				'role_id' => $role->getId(),
			];
		}
		try {
			$this->dibi->begin();
			$this->saveData($user);
			$this->dibi->delete($this->userRolesTable)->where('user_id = %i', $user->getId())->execute();
			$this->dibi->insert($this->userRolesTable, $data)->execute();
			$this->dibi->commit();
		} catch (\Throwable $th) {
			throw $th;
			return false;
		}
		return true;
	}
}
