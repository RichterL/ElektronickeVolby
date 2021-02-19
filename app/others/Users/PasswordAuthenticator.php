<?php

use Nette\Database\Explorer;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

class PasswordAuthenticator implements Nette\Security\Authenticator
{

	private $database;
	private $passwords;

	public function __construct(Explorer $explorer, Passwords $passwords) {
		$this->database = $explorer;
		$this->passwords = $passwords;
	}

	public function authenticate(string $username, string $password): SimpleIdentity
	{
		$user = $this->database->table('users')
			->where('username', $username)
			->fetch();

		if (!$user) {
			throw new Nette\Security\AuthenticationException('User not found.');
		}

		if (!$this->passwords->verify($password, $user->password)) {
			throw new Nette\Security\AuthenticationException('Invalid password.');
		}

		foreach ($user->related('users_roles') as $userRole) {
			$roles[] = $userRole->role->key;
		}

		return new SimpleIdentity(
			$user->id,
			$roles,
			['name' => $user->username]
		);
	}
}
