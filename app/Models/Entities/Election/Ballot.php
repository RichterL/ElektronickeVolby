<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use App\Models\Traits\Entity\HasId;

/**
 * @property ElectionId $election
 */
abstract class Ballot extends Entity implements IdentifiedById
{
	use HasId;

	protected ?ElectionId $election = null;

	public function getElection(): ?ElectionId
	{
		return $this->election;
	}
}
