Electronické volby
=================

Tato aplikace vznikla jako výsledek bakalářské práce. Text bakalářské práce bude zpřístupněn až po jejím zveřejnění.

Systémové požadavky
------------

Aplikace ke svému provozu vyžaduje:

- HTTP server - otestován nginx a Apache2
- aktivní SSL šifrování (HTTPS) - pro testování stačí self-signed certifikát
- Databázový server - otestováno MySQL a MariaDb, vyžadována konfigurace `secure_file_priv = ""`
- PHP verze 7.4 (verze 8.0 netestována, teoreticky funkční)
- PHP rozšíření php-ldap (php-gmp doporučeno)
- Composer (správa PHP balíčků) - seznam balíčků je níže, instalace je automatická



Základní instalace
------------

Následujícím příkazem nainstalujte celý projekt včetně balíčků závislostí:

	composer create-project richterl/elektronicke-volby /path/to/install
  
- Virtual host HTTP serveru musí směřovat pouze na adresáře `www` a `www_backend`.
- Především adresáře `app` a `log` a `temp` *nesmí* být přístupné z prohlížeče! (vizte [Nette security warning](https://nette.org/cs/security-warning))
- Adresáře `log` a `temp` musí být zapisovatelné pro všechny (world-writable)
- Soubor `app/config/local.neon.default` obsahuje přednastavené hodnoty pro připojení k univerzitnímu LDAP serveru (dostupný pouze v rámci sítě UTB) a konfiguraci připojení k databázi - tu je potřeba doplnit. Upravený soubor přejmenujte na `local.neon`
- V adresáři `bin` naleznete soubory pro základní zprovoznění databáze. `export.sql` (struktura) a `install.sql` - hodnoty vyžadované pro základní běh aplikace (administrátorský účet, ACL).
- Upravte soubor `app/Router/RouterFactory.php` tak, aby reflektoval skutečný stav.
- Aplikace by nyní měla být funkční a dostupná na adresách `https://admin.volby.l` a `https://volby.l` (pro lokální instalaci)
