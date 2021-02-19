<?php

namespace Models;

use Nette\Database\Connection;
use Nette\Database\Explorer;

abstract class BaseModel
{
	protected $database;

	public function __construct(Explorer $database) {
		$this->database = $database;
	}


}
