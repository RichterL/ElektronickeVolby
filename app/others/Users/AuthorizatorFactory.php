<?php

use Models\AclModel;
use Nette\Security\Authorizator;
use Nette\Security\Permission;
use Repositories\PrivilegeRepository;
use Repositories\ResourceRepository;
use Repositories\RoleRepository;

class AuthorizatorFactory
{
	const RULE_TYPE_ALLOW = 1;
	const RULE_TYPE_DENY = 2;

	private $model;
	private RoleRepository $roleRepository;
	private ResourceRepository $resourceRepository;
	private PrivilegeRepository $privilegeRepository;

	public function __construct(
		AclModel $model,
		RoleRepository $roleRepository,
		ResourceRepository $resourceRepository,
		PrivilegeRepository $privilegeRepository
	) {
		$this->model = $model;
		$this->roleRepository = $roleRepository;
		$this->resourceRepository = $resourceRepository;
		$this->privilegeRepository = $privilegeRepository;
	}

	public function create(): Authorizator
	{
		$authorizator = new Permission();

		// $roles = $this->roleRepository->findAll();

		foreach ($this->model->getRoles() as $roleId => $role) {
			$authorizator->addRole($role->key, $role->parent ?? null);
		}
		foreach ($this->model->getResources() as $resourceId => $resource) {
			$authorizator->addResource($resource->key, $resource->parent);
		}

		$rules = $this->model->getRules();
		foreach ($rules as $ruleId => $rule) {
			$role = $rule->ref('role')->key;
			$resource = $rule->ref('resource')->key;
			$privilege = $rule->ref('privilege')->key;
			switch ($rule->type) {
				case self::RULE_TYPE_ALLOW:
					$authorizator->allow($role, $resource, $privilege);
					break;
				case self::RULE_TYPE_DENY:
					$authorizator->deny($role, $resource, $privilege);
					break;
			}
		}
		$authorizator->allow('superAdmin');

		return $authorizator;
	}
}
