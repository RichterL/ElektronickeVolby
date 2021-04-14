<?php
declare(strict_types=1);

namespace App\Models\Mappers\Db;

class Tables
{
	public const
		ACL_ROLES = 'acl_role',
		ACL_RESOURCES = 'acl_resource',
		ACL_RESOURCE_PRIVILEGE = 'acl_resource_privilege',
		ACL_RULES = 'acl_rule',
		ELECTION = 'election',
		VOTER = 'voter',
		VOTER_FILE = 'voter_file',
		QUESTION = 'question',
		ANSWER = 'answer',
		BALLOT = 'ballot',
		USERS = 'user',
		USERS_ROLES = 'user_roles';
}
