<?php


namespace App\Models\Mappers;


interface TransactionableMapper
{
	public function beginTransaction(): void;

	public function finishTransaction(): void;

	public function rollbackTransaction(): void;
}