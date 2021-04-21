<?php
declare(strict_types=1);

namespace App\Models\Entities;

use Iterator;
/** @extends Iterator<int, Entity> */
interface EntityIterator extends Iterator
{
	public function current(): Entity;

	public function next(): void;

	public function key(): int;

	public function valid(): bool;

	public function rewind(): void;
}
