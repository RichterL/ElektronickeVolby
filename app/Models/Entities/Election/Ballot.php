<?php

namespace App\Models\Entities\Election;

use Models\Entities\Election\Election;
use Models\Entities\IdentifiedById;
use Models\Traits\Entity\HasId;

/**
 * @property Election $election
 */
abstract class Ballot extends \Models\Entities\Entity implements IdentifiedById
{
	use HasId;

	protected Election $election;
}
