<?php
declare(strict_types=1);

namespace App\Core\Classes;

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
		$user = $this->userRepository->findByUsername($username);
		if (!$user) {
			throw new Nette\Security\AuthenticationException('User not found.');
		}
		if (!$this->passwords->verify($password, $user->getPassword())) {
			throw new Nette\Security\AuthenticationException('Invalid password.');
		}
		return $user;
	}
}
