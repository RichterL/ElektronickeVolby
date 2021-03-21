<?php

namespace LDAP;

use Tracy\Logger;

class Service extends \Nette\DI\CompilerExtension
{
	private string $baseDn;
	private $connection;

	public function __construct(string $server, string $baseDn)
	{
		$this->baseDn = $baseDn;
		$this->connection = ldap_connect($server);
		ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
	}

	public function getRemoteUser(string $username, string $password): ?array
	{
		set_error_handler([$this, 'handleErrors']);

		$filter = sprintf('(cn=%s)', $username);
		$ldaprdn = sprintf('cn=%s,%s', $username, $this->baseDn);

		ldap_bind($this->connection, $ldaprdn, $password);

		$res = ldap_search($this->connection, $this->baseDn, $filter);
		$ary = ldap_get_entries($this->connection, $res);
		$mail = $ary[0]['mail'][0];
		$name = $ary[0]['givenname'][0];
		$surname = $ary[0]['sn'][0];
		$fullname = $ary[0]['displayname'][0];
		$department = $ary[0]['department'][0];
		$type = $ary[0]['employeetype'][0];
		$guid = $ary[0]['objectguid'][0];
		$guidDecoded = base64_encode($guid);
		$utbid = $ary[0]['employeeid'][0];

		ldap_close($this->connection);
		restore_error_handler();
		$roles = [];
		switch ($type) {
			case 'stu':
				$roles[] = 'student';
				break;

			case 'zam':
				$roles[] = 'employee';
				break;
		}
		return [
			'fullname' => $fullname,
			'email' => $mail,
			'id' => $utbid,
			'roles' => $roles,
		];
	}

	public function handleErrors(int $errno, string $errstr, ?string $errfile = null, ?int $errline = null)
	{
		if ($errstr == "ldap_bind(): Unable to bind to server: Can't contact LDAP server") {
			$this->log('LDAP server unreachable');
			throw new NoConnectionException('Unable to connect to LDAP server');
		}

		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting
			return;
		}

		if (0 === error_reporting()) {
			// error was suppressed with the @-operator
			return false;
		}

		throw new LdapException($errstr, 0, $errno, $errfile, $errline);
	}

	public function log(string $message)
	{
		$logger = new Logger(APP_DIR . '/../log/');
		$logger->log($message, Logger::CRITICAL);
	}
}
