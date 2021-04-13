<?php

namespace App\Models\Factories;

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
		$role = $this->roleRepository->findById($values['role']);
		$resource = $this->resourceRepository->findById($values['resource']);
		$privilege = $this->privilegeRepository->findById($values['privilege']);
		$rule->setRole($role);
		$rule->setResource($resource);
		$rule->setPrivilege($privilege);
		if (!empty($values['id'])) {
			$rule->setId($values['id']);
		}
		$type = Type::fromValue($values['type']);
		$rule->setType($type);
		return $rule;
	}
}
