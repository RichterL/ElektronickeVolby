<?php
declare(strict_types=1);

namespace App\Core\Classes;

use App\Core\Classes\LDAP\LdapException;
use App\Core\Classes\LDAP\Service;
use App\Models\Entities\User;
use Nette\Security\IIdentity;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Nette;

class LdapAuthenticator implements Nette\Security\Authenticator
{
	private UserRepository $userRepository;
	private RoleRepository $roleRepository;
	private Service $ldapService;

	public function __construct(UserRepository $userRepository, RoleRepository $roleRepository, Service $service)
	{
		$this->userRepository = $userRepository;
		$this->roleRepository = $roleRepository;
		$this->ldapService = $service;
	}

	public function authenticate(string $username, string $password): IIdentity
	{
		try {
			$remoteUser = $this->ldapService->getRemoteUser($username, $password);
		} catch (LdapException $ex) {
			throw new Nette\Security\AuthenticationException('Authentication failed.');
		}

		$user = $this->userRepository->findByUsername($remoteUser['email']);
		if ($user) {
			return $user;
		}
		$user = new User();

		$roles = $this->roleRepository->findByKey(reset($remoteUser['roles']));
		$user->setEmail($remoteUser['email'])->setName($remoteUser['fullname'])->setUsername($remoteUser['email'])->setRoles(...$roles);
		return $user;
	}
}
