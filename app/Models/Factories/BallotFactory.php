<?php
declare(strict_types=1);

namespace App\Models\Factories;

use App\Models\Entities\Election\Ballot;
use App\Models\Entities\Election\DecryptedBallot;
use App\Models\Entities\Election\ElectionId;
use App\Models\Entities\Election\EncryptedBallot;

class BallotFactory
{
	public static function create(array $data): Ballot
	{
		if (empty($data['decryptedAt'])) {
			$ballot = new EncryptedBallot();
		} else {
			$ballot = new DecryptedBallot();
		}
		$data['election'] = ElectionId::fromValue($data['election']);
		$ballot->setValues($data);
		return $ballot;
	}
}
