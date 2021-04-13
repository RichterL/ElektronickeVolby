<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use dibi;
use Dibi\DriverException;
use Exception;
use Models\Entities\User;
use Models\Mappers\Db\Tables;
use Models\Mappers\IUserMapper;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

class UserMapper extends BaseMapper implements IUserMapper
{
	const MAP = [
		'id' => 'id',
		'username' => 'username',
		'password' => 'password',
		'name' => 'name',
		'surname' => 'surname',
		'email' => 'email',
	];

	protected string $table = Tables::USERS;
	protected string $userRolesTable = Tables::USERS_ROLES;
	private RoleMapper $roleMapper;

	public function __construct(RoleMapper $roleMapper)
	{
		$this->roleMapper = $roleMapper;
	}

	public function create(array $data = [], $includeRoles = false): User
	{
		$user = new User();
		if (!empty($data)) {
			foreach (self::MAP as $property => $key) {
				if (!empty($data[$key])) {
					$user->$property = $data[$key];
				}
			}
		}
		return $user;
	}

	/**
	 * @throws SavingErrorException
	 */
	public function save(User $user): bool
	{
		try {
			$this->dibi->begin();
			$this->saveWithId($user);
			$data = [];
			foreach ($user->getRoles(true) as $role) {
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
			return true;
		} catch (DriverException $e) {
			throw new SavingErrorException('Saving failed!');
		}
	}

	public function getDataSource(array $filter = []): DibiFluentDataSource
	{
		$fluent = $this->dibi->select('u.`id`, u.`username`, u.`email`, u.`name`, u.`surname`, u.`full_name`, GROUP_CONCAT(ar.`key`) AS `roles`')
			->from('%n u', Tables::USERS)
			->leftJoin('%n ur', Tables::USERS_ROLES)->on('ur.user_id = u.id')
			->leftJoin('%n ar', Tables::ACL_ROLES)->on('ur.role_id = ar.id')
			->groupBy('u.`id`');
		return new DibiFluentDataSource($fluent, 'id');
	}

	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): User
	{
		return parent::findOne($filter);
	}

	/** @return User[] */
	public function findAll(): array
	{
		try {
			return $this->cache->load('user.findAll', function () {
				throw new Exception('error');
				return parent::findAll();
			});
		} catch (\Throwable $e) {
			return parent::findAll();
		}
	}
}
