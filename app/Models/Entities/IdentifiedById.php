<?php
declare(strict_types=1);

namespace Models\Entities;

interface IdentifiedById
{
	public function getId(): ?int;

	public function setId(int $id): self;
}
