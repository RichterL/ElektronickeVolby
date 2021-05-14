<?php

namespace App\Repositories;


interface TransactionableRepository
{
	public function beginTransaction(): void;

	public function finishTransaction(): void;

	public function rollbackTransaction(): void;
}