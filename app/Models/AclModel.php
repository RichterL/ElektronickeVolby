<?php

namespace Models;

class AclModel extends BaseModel
{
	public function getRoles()
	{
		return $this->database->table(Tables::ACL_ROLES)->fetchAll();
	}

	public function getResources()
	{
		return $this->database->table(Tables::ACL_RESOURCES)->fetchAll();
	}

	public function getRules()
	{
		return $this->database->table(Tables::ACL_RULES)->fetchAll();
	}
}
