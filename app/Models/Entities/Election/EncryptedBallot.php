<?php

namespace App\Models\Entities\Election;

use Models\Entities\Election\Election;
use Models\Traits\Entity\HasId;

/**
 * @property string $encryptedData
 * @property string $encryptedKey
 * @property string $hash
 * @property string $signature
 */
class EncryptedBallot extends Ballot
{
	protected string $encryptedData;
	protected string $encryptedKey;
	protected string $hash;
	protected string $signature;
}
