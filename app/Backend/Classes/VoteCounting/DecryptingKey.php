<?php

namespace App\Backend\Classes\VoteCounting;

use phpseclib3\Crypt\AES;

class DecryptingKey extends AES
{
	private const TAG_LENGTH = 16;

	public function __construct(string $key, string $iv)
	{
		parent::__construct('gcm');
		$this->setKey(base64_decode($key));
		$this->setNonce(base64_decode($iv));
	}

	public function decrypt($ciphertext): string
	{
		$this->setTag(substr($ciphertext, -self::TAG_LENGTH));
//		$this->decrypt(substr($ciphertext, 0, -self::TAG_LENGTH));
		return parent::decrypt(substr($ciphertext, 0, -self::TAG_LENGTH));
	}
}
