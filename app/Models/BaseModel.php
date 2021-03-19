<?php

namespace Models;

use Dibi\Connection as DibiConnection;
use Nette\Database\Connection;
use Nette\Database\Explorer;

abstract class BaseModel
{
    protected $database;
    protected $dibi;

    public function __construct(Explorer $database, DibiConnection $dibi)
    {
        $this->database = $database;
        $this->dibi = $dibi;
    }
}
