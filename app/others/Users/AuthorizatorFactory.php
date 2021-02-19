<?php

use Models\AclModel;
use Nette\Security\Authorizator;
use Nette\Security\Permission;

class AuthorizatorFactory
{
	const RULE_TYPE_ALLOW = 1;
	const RULE_TYPE_DENY = 2;

	private $model;

	public function __construct(AclModel $model) {
		$this->model = $model;
	}

	public function create(): Authorizator
	{
		$authorizator = new Permission();


		foreach ($this->model->getRoles() as $roleId => $role) {
			$authorizator->addRole($role->key, $role->parent ?? null);
		}
		foreach ($this->model->getResources() as $resourceId => $resource) {
			$authorizator->addResource($resource->key, $resource->parent);
		}

		$rules = $this->model->getRules();
		foreach ($rules as $ruleId => $rule) {
			$role = $rule->ref('roles')->key;
			$resource = $rule->ref('resources')->key;
			$privilege = $rule->ref('privileges')->key;
			switch($rule->type) {
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
