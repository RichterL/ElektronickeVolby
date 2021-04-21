<?php
declare(strict_types=1);

namespace App\Models\Factories;

use App\Models\Entities\Rule\Rule;
use App\Models\Entities\Rule\Type;
use App\Repositories\PrivilegeRepository;
use App\Repositories\ResourceRepository;
use App\Repositories\RoleRepository;
use App\Repositories\RuleRepository;

class RuleFactory
{
	private RoleRepository $roleRepository;
	private ResourceRepository $resourceRepository;
	private PrivilegeRepository $privilegeRepository;
	private RuleRepository $ruleRepository;

	public function __construct(
		RoleRepository $roleRepository,
		ResourceRepository $resourceRepository,
		PrivilegeRepository $privilegeRepository,
		RuleRepository $ruleRepository
	) {
		$this->roleRepository = $roleRepository;
		$this->resourceRepository = $resourceRepository;
		$this->privilegeRepository = $privilegeRepository;
		$this->ruleRepository = $ruleRepository;
	}

	public function createFromValues(array $values)
	{
		$rule = new Rule();
		$role = $this->roleRepository->findById((int) $values['role']);
		$resource = $this->resourceRepository->findById((int) $values['resource']);
		$privilege = $this->privilegeRepository->findById((int) $values['privilege']);
		$rule->setRole($role);
		$rule->setResource($resource);
		$rule->setPrivilege($privilege);
		if (!empty($values['id'])) {
			$rule->setId((int) $values['id']);
		}
		$type = Type::fromValue($values['type']);
		$rule->setType($type);
		return $rule;
	}
}
