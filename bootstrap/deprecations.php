<?php

// Laravel merges its own vendor config/database.php defaults on every boot
// (see LoadConfiguration::getBaseConfiguration()), and that vendor file still
// references the PDO::MYSQL_ATTR_SSL_CA constant deprecated in PHP 8.4+. We
// can't patch the vendor file (composer update would revert it), so this
// silences that one exact upstream notice while leaving every other
// deprecation warning visible as normal.
set_error_handler(function (int $errno, string $errstr): bool {
    return str_contains($errstr, 'PDO::MYSQL_ATTR_SSL_CA');
}, E_DEPRECATED);
