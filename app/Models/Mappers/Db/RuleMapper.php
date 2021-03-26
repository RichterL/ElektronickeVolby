<?php

declare(strict_types=1);

namespace Models\Mappers\Db;

use dibi;
use Dibi\Connection;
use Exception;
use Models\Entities\Role\Role;
use Models\Entities\Rule\Rule;
use Models\Entities\Rule\RuleCollection;
use Models\Entities\Rule\Type;
use Models\Mappers\BaseMapper;
use Models\Mappers\IRuleMapper;
use Models\Mappers\RoleMapper;
use Utils\ValueObject\ValueObject;

class RuleMapper extends BaseMapper implements IRuleMapper
{
	const MAP = [
		'id' => 'id',
		'roleId' => 'role_id',
		'resourceId' => 'resource_id',
		'privilegeId' => 'privilege_id',
		'type' => 'type',
	];

	protected $table = Tables::ACL_RULES;
	private RoleMapper $roleMapper;
	private ResourceMapper $resourceMapper;
	private PrivilegeMapper $privilegeMapper;

	public function __construct(
		Connection $dibi,
		RoleMapper $roleMapper,
		ResourceMapper $resourceMapper,
		PrivilegeMapper $privilegeMapper
	) {
		parent::__construct($dibi);
		$this->roleMapper = $roleMapper;
		$this->resourceMapper = $resourceMapper;
		$this->privilegeMapper = $privilegeMapper;
	}

	public function create(array $data = []): Rule
	{
		$rule = new Rule();
		if (!empty($data)) {
			$rule->setId($data['id']);
			$rule->setRole($this->roleMapper->findOne(['id' => $data['role_id']]));
			$rule->setResource($this->resourceMapper->findOne(['id' => $data['resource_id']]));
			$rule->setPrivilege($this->privilegeMapper->findOne(['id' => $data['privilege_id']]));
			$rule->type = Type::fromValue($data['type']);
		}
		return $rule;
	}

	public function save(Rule $rule): bool
	{
		$data = [];
		foreach (self::MAP as $property => $key) {
			if (isset($rule->$property)) {
				if ($rule->$property instanceof ValueObject) {
					$data[$key] = $rule->$property->getValue();
					continue;
				}
				$data[$key] = $rule->$property;
			}
		}

		unset($data['id']);
		$id = $rule->getId();
		if (empty($id)) {
			$id = $this->dibi->insert($this->table, $data)->execute(dibi::IDENTIFIER);
			if (!$id) {
				throw new Exception('insert failed');
			}
			$rule->setId($id);
			return true;
		}

		$this->dibi->update($this->table, $data)->where('id = %i', $id)->execute();
		return true;
	}

	/** @var Rule[] */
	public function findRelated(Role $role): RuleCollection
	{
		$rules = new RuleCollection();
		$result = $this->dibi->select('*')
			->from($this->table)
			->where('role_id = %i', $role->getId())
			->fetchAssoc('id,=');
		foreach ($result as $id => $values) {
			$rules[] = $this->create($values)->setId($id);
		}
		return $rules;
	}

	/** parent concrete implementetions */
	public function findOne(array $filter = []): ?Rule
	{
		return parent::findOne($filter);
	}

	/** @return Rule[] */
	public function findAll(): array
	{
		return parent::findAll();
	}
}
