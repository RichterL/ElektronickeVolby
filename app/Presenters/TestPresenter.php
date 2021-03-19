<?php

declare(strict_types=1);

namespace App\Presenters;

use Crypto\BlindSignature\PublicKey as BlindSignaturePublicKey;
use Nette;
use Nette\Application\Responses\JsonResponse;
use Nette\PhpGenerator\Dumper;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\Rijndael;
use phpseclib3\Math\BigInteger;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\Formats\Keys\Raw;
use phpseclib3\Crypt\RSA\PrivateKey;
use phpseclib3\Crypt\RSA\PublicKey;
use ReflectionClass;

final class TestPresenter extends Nette\Application\UI\Presenter
{
	private PrivateKey $private;
	private PublicKey $public;

	public function __construct()
	{
	}

	public function renderDefault(): void
	{
		$private = RSA::createKey();
		$public = $private->getPublicKey();
		//$private->loadParameters();
		dump($private);
		dump($public);

		dump($private->__toString());

		dump($public->__toString());

		$parts = $public->toString('raw');
		dump($parts);

		$n = $parts['n'];
		$e = $parts['e'];
		$bigOne = new BigInteger(1);
		do {
			$r = BigInteger::randomPrime(64);
			$gcd = $r->gcd($n);
		} while (
			!$gcd->equals($bigOne) ||
			$r->compare($n) >= 0 ||
			$r->compare($bigOne) <= 0
		);
		dump($r->toString());

		$plaintext = 'message';
		$hash = md5($plaintext);
		$hashInt = new BigInteger($hash, 16);
		dump($hashInt->toString());

		// $blinded = $r->modPow($e, $n)->multiply($hashInt)->modPow($bigOne, $n);
		list($q, $rem) = ($r->modPow($e, $n)->multiply($hashInt))->divide($n);

		dump($r->modPow($e, $n)->toHex());
		dump($r->modPow($e, $n)->multiply($hashInt)->toHex());
		dump($rem->toHex());
		dump($rem->modInverse($n)->toHex());

		$signed = $rem;

		// $unblinded = $signed->multiply($r->modInverse($n))->modPow($bigOne, $n);
		list($q, $unblinded) = $signed->multiply($r->modInverse($n))->divide($n);
		dump($unblinded->toString());

		// const unblinded = signed.multiply(r.modInverse(N)).mod(N);

		// const mu = r.modPow(bigE, bigN).multiply(messageHash).mod(bigN); // Alice computes mu = H(msg) * r^e mod N
	}

	public function actionInt()
	{
		$ten = new BigInteger(10);
		dump($ten->toString());
		$twenty = new BigInteger(20);
		dump($twenty->toString());
		$div = $ten->divide($twenty);
		dump($div[0]->toString());
		$modulo = $div[1];
		dump($modulo->toString());
		dump(($modulo->modInverse(new BigInteger(0))->toString()));
		die;
	}

	public function actionTwo()
	{
		$private = RSA::createKey();
		$public = $private->getPublicKey();
		//$private->loadParameters();
		dump($private);
		dump($public);

		dump($private->__toString());

		dump($public->__toString());

		$parts = $public->toString('raw');
		dump($parts);

		$n = $parts['n'];
		$e = $parts['e'];
		// $bigOne = new BigInteger(1);
		// do {
		// 	$r = BigInteger::randomPrime(64);
		// 	$gcd = $r->gcd($n);
		// } while (
		// 	!$gcd->equals($bigOne) ||
		// 	$r->compare($n) >= 0 ||
		// 	$r->compare($bigOne) <= 0
		// );
		$r = $this->getR($n);
		dump($r->toString());

		$plaintext = 'message';
		$hash = md5($plaintext);
		$hashInt = new BigInteger($hash, 16);
		dump($hashInt->toString());

		$rmod = $r->modPow($e, $n);
		list($q, $rem) = $rmod->divide($n);
		$blinded = $hashInt->multiply($rem);

		dump($rem->toHex());
		dump($rem->modInverse($n)->toHex());

		$signed = $rem;

		// $unblinded = $signed->multiply($r->modInverse($n))->modPow($bigOne, $n);
		list($q, $unblinded) = $signed->multiply($r->modInverse($n))->divide($n);
		dump($unblinded->toString());
		die;
	}

	public function getR(BigInteger $n)
	{
		$bigOne = new BigInteger(1);
		// while (true) {
		// 	$r = BigInteger::randomPrime(8);
		// 	$gcd = $r->gcd($n);
		// 	$equalsOne = $gcd->equals($bigOne);
		// 	$greaterThanOne = $r->compare($bigOne);
		// 	$lessThanN = $r->compare($n);
		// 	$decimal = $r->toString();
		// }

		do {
			$r = BigInteger::randomPrime(8);
			$gcd = $r->gcd($n);
		} while (
			!$gcd->equals($bigOne) ||
			$r->compare($n) >= 0 ||
			$r->compare($bigOne) <= 0
		);
		return $r;
	}

	public function actionSmall()
	{
		// $n = BigInteger::randomPrime(8);
		$n = new BigInteger(173);
		$nDecimal = $n->toString();
		// $e = BigInteger::randomPrime(8);
		$e = new BigInteger(157);
		$eDecimal = $e->toString();
		// $r = $this->getR($n);
		$r = new BigInteger(167);
		$rDecimal = $r->toString();

		$message = 'Hello World!';
		$hash = md5($message);
		$hashInt = new BigInteger($hash, 16);
		$hashIntDecimal = $hashInt->toString();

		$rmod = $r->modPow($e, $n);
		$rmodDecimal = $rmod->toString();
		list($q, $rem) = $rmod->divide($n);
		$remDecimal = $rem->toString();
		$blinded = $hashInt->multiply($rem);
		$blindedDecimal = $blinded->toString();

		dump($rem->toHex());
		dump($rem->modInverse($n)->toHex());

		$signed = $blinded;

		// $unblinded = $signed->multiply($r->modInverse($n))->modPow($bigOne, $n);
		$rmodInverse = $r->modInverse($n);
		$rmodInverseDecimal = $rmodInverse->toString();
		$unblinded = $signed->multiply($rmodInverse);
		$unblindedDecimal = $unblinded->toString();

		list($q, $unblinded) = $signed->multiply($r->modInverse($n))->divide($n);
		dump($unblinded->toString());
		die;
	}

	public function actionSimple()
	{
		$plaintext = 'Exercitation officia ex minim in sint proident tempor ipsum eu laborum mollit id minim consequat. Esse excepteur adipisicing minim irure esse proident in laborum officia labore proident occaecat amet. Deserunt incididunt dolore amet consectetur sit.';
		$public = $this->getPublicKey();
		$ciphertext = $public->encrypt($plaintext);
	}

	public function actionKey()
	{
		$this->private = RSA::createKey();
		$this->public = $this->private->getPublicKey();
		$parts = $this->public->toString('raw');
		$this->template->n = $parts['n']->toString();
		$this->template->e = $parts['e']->toString();
	}

	public function actionDirect()
	{
	}

	public function handleGetPrivateKey()
	{
		$private = $this->getPrivateKey();

		$response = $private->__toString();
		$this->sendResponse(new JsonResponse($response));
	}

	public function getPrivateKey(): PrivateKey
	{
		$privatefile = file_get_contents('../keys/key.pem');
		$private = PublicKeyLoader::load($privatefile);
		return $private;
	}

	public function getPublicKey(): PublicKey
	{
		$publicFile = file_get_contents('../keys/public.pem');
		$publicKey = PublicKeyLoader::load($publicFile);
		return $publicKey;
	}

	public function handleGetPublicSigningKey()
	{
		$publicfile = file_get_contents('../keys/public.pem');
		$public = PublicKeyLoader::load($publicfile);
		$parts = $public->toString('raw');
		$response = ['n' => $this->base64_encode_url($parts['n']->toBytes()), 'e' => $this->base64_encode_url($parts['e']->toBytes()), 'key' => $public->__toString()];
		$this->sendJson($response);
	}

	public function base64_encode_url($string)
	{
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
	}

	public function base64_decode_url($string)
	{
		return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
	}

	public function handleGetPublicEncryptionKey()
	{
		$publicfile = file_get_contents('../keys/public.pem');
		$public = PublicKeyLoader::load($publicfile);
		$key = $public->__toString();
		$this->sendJson($key);
	}

	public function handleSign($body)
	{
		$request = $this->getHttpRequest();
		$raw = $request->getRawBody();
		$parsed = json_decode($raw, true);
		$num = implode($parsed['blinded']['blinded']);
		$blinded = new BigInteger($num);
		$signed = base64_encode($this->private->sign($blinded));
		$response = ['status' => 'ok', 'signed' => $signed];

		$this->sendJson($response);
		// $this->terminate(new JsonResponse(json_encode($response)));
	}

	public function handleDecrypt()
	{
		$request = $this->getHttpRequest();
		$raw = $request->getRawBody();
		$parsed = json_decode($raw, true);

		$privateKey = $this->getPrivateKey();
		$privateKeyString = $privateKey->toString('PKCS1');
		$decoded = base64_decode($parsed['message']);
		$decrypted = $privateKey->decrypt($decoded);
		$response = ['status' => 'ok', 'decrypted' => $decrypted, 'parsed' => $parsed['message']];

		$this->sendJson($response);
	}

	public function handleDecryptPKCS1()
	{
		$request = $this->getHttpRequest();
		$raw = $request->getRawBody();
		$parsed = json_decode($raw, true);

		$privateKey = $this->getPrivateKey()->withPadding(RSA::ENCRYPTION_PKCS1);
		$decoded = base64_decode($parsed['message']);
		$decrypted = $privateKey->decrypt($decoded);
		$response = ['status' => 'ok', 'decrypted' => $decrypted, 'parsed' => $parsed['message']];

		$this->sendJson($response);
	}

	public function handleDecryptBlinded($body)
	{
		$request = $this->getHttpRequest();
		$raw = $request->getRawBody();
		$parsed = json_decode($raw, true);
		$num = implode($parsed['unblinded']);
		$hashedMessage = hash('sha256', $parsed['message']);
		$hashInt = new BigInteger($hashedMessage, 16);
		$signedHash = base64_encode($this->private->sign($hashInt));
		$unblinded = new BigInteger($num);
		$unblindedText = $unblinded->toString();
		$verify = $this->public->verify($hashInt, $unblinded);

		$response = ['status' => 'ok', 'verified' => $signedHash == $unblindedText];
		$this->sendJson($response);
	}

	public function actionSodium()
	{
		// echo sodium_version_string();
	}

	public function handleSave()
	{
		$request = $this->getHttpRequest();
		$raw = $request->getRawBody();
		$parsed = json_decode($raw, true);

		$key = base64_decode($parsed['key']);
		$message = base64_decode($parsed['message']);

		$privateKey = $this->getPrivateKey();
		$decryptingKey = json_decode($privateKey->decrypt($key));

		$decrypted = '';
		// echo($decryptingKey->key);
		// die;
		$aes = new AES('gcm');
		// $aes->setPreferredEngine('PHP');
		$aes->setKey(base64_decode($decryptingKey->key));
		$aes->setNonce(base64_decode($decryptingKey->iv));
		$aes->setTag(substr($message, -16));
		$decrypted = $aes->decrypt(substr($message, 0, strlen($message) - 16));

		$response = ['status' => 'ok', 'decryptedKey' => $decryptingKey, 'decryptedMessage' => ['original' => $decrypted, 'base64' => base64_encode($decrypted)]];
		$this->sendJson($response);
	}

	// function getAesTag($encrypted, $tagLength = 128) {
	// 	return $encrypted.slice(encrypted.byteLength - ((tagLength + 7) >> 3))
	// }
}
