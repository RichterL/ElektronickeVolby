<?php
ini_set('display_errors', true);
error_reporting(E_ALL);
$login = 'l_richter';
$heslo = 'gRK7tlq7Oc';
$rc = ldap_get_rc($login, $heslo);

if ($rc) {
	$_SESSION['rc'] = $rc;
} else {
	$sent = '';

	$zobrazit_formular = true;

	$akce = '<span class="oramovani-cerne">&nbsp; Špatné jméno nebo heslo. &nbsp; Wrong login name or password.</span>';

	$spatneheslo = 'true';
};

function ldap_get_rc($username, $password)
{
	$ldapServer = 'ldap://10.96.2.201:389';

	$ldap = ldap_connect($ldapServer);

	$filter = "(cn=${username})";

	$base_dn = 'OU=uzivatele,OU=ucty,OU=UTB,DC=ad,DC=utb,DC=cz';

	$ldaprdn = "cn=${username},${base_dn}";

	ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

	ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

	@$bind = ldap_bind($ldap, $ldaprdn, $password);

	if ($bind) {
		@$res = ldap_search($ldap, $base_dn, $filter);

		@$ary = ldap_get_entries($ldap, $res);

		$rc = $ary[0]['utbid'][0];

		@ldap_close($ldap);

		return $rc;
	}

	return 0;
}
