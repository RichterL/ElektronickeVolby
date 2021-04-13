<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

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
