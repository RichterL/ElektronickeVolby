<?php
declare(strict_types=1);

namespace Models\Mappers\Db;

class Tables
{
	const
		ACL_ROLES = 'acl_role',
		ACL_RESOURCES = 'acl_resource',
		ACL_RESOURCE_PRIVILEGE = 'acl_resource_privilege',
		ACL_RULES = 'acl_rule',
		ELECTION = 'election';

	const USERS = 'user';
	const USERS_ROLES = 'user_roles';
}
