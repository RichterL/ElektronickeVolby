<?php
declare(strict_types=1);

namespace App\Core\Classes;

use App\Models\Mappers\Exception\EntityNotFoundException;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use App\Repositories\UserRepository;
use Nette;

class PasswordAuthenticator implements Nette\Security\Authenticator
{
	private Passwords $passwords;
	private UserRepository $userRepository;

	public function __construct(Passwords $passwords, UserRepository $userRepository)
	{
		$this->passwords = $passwords;
		$this->userRepository = $userRepository;
	}

	public function authenticate(string $username, string $password): IIdentity
	{
		try {
			$user = $this->userRepository->findByUsername($username);
		} catch (EntityNotFoundException $e) {
			throw new Nette\Security\AuthenticationException('User not found.');
		}
		if (!$this->passwords->verify($password, $user->getPassword())) {
			throw new Nette\Security\AuthenticationException('Invalid password.');
		}
		return $user;
	}
}
