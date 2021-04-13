<?php

use Nette\Security\Authorizator;
use Nette\Security\Permission;
use App\Repositories\ResourceRepository;
use App\Repositories\RoleRepository;

class AuthorizatorFactory
{
	private RoleRepository $roleRepository;
	private ResourceRepository $resourceRepository;

	public function __construct(
		RoleRepository $roleRepository,
		ResourceRepository $resourceRepository
	) {
		$this->roleRepository = $roleRepository;
		$this->resourceRepository = $resourceRepository;
	}

	public function create(): Authorizator
	{
		$authorizator = new Permission();
		foreach ($this->resourceRepository->findAll() as $resource) {
			$authorizator->addResource($resource->key, $resource->parent->key ?? null);
		}
		foreach ($this->roleRepository->findAll(true) as $role) {
			$authorizator->addRole($role->key, $role->parent->key ?? null);
			$rules = $role->rules->getByTypes();
			foreach ($rules as $type => $ruleResources) {
				foreach ($ruleResources as $ruleResource => $rulePrivileges) {
					$authorizator->$type($role->key, $ruleResource, $rulePrivileges);
				}
			}
		}

		// allow all resources and privileges for superAdmin
		$authorizator->allow('superAdmin');

		return $authorizator;
	}
}
