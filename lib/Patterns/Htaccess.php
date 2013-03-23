<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2012 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8.1 beta
 * 
 * PHP version >= 5.2.3
 * 
 */


class Patterns_Htaccess implements Patterns_Interface
{
    /**
     * pattern
     * 
     * @return array
     */
    public static function get ()
    {
        return array(
            'Production' => '<Files ~ ".dat$|.inc$|.ini$|.cfg$|.log$|.class.php$|.inc.php$|config.php$">
Order allow,deny
Deny from All
Satisfy All
</Files>

DirectoryIndex index.php index.html

php_value default_mimetype text/html

php_flag register_globals Off

php_flag magic_quotes_gpc Off
php_flag magic_quotes_runtime Off
php_flag magic_quotes_sybase Off

# Debug
php_value error_reporting -1
php_flag display_errors Off
php_flag log_errors On


Options -Indexes

php_value default_charset UTF-8
AddDefaultCharset UTF-8

ErrorDocument 403 /
ErrorDocument 404 /',
            'Development' => '<Files ~ ".dat$|.inc$|.ini$|.cfg$|.log$|.class.php$|.inc.php$|config.php$">
Order allow,deny
Deny from All
Satisfy All
</Files>

DirectoryIndex index.php index.html

php_value default_mimetype text/html

php_flag register_globals Off

php_flag magic_quotes_gpc Off
php_flag magic_quotes_runtime Off
php_flag magic_quotes_sybase Off


# Debug
php_value error_reporting -1
php_flag display_errors On
php_flag log_errors Off


Options -Indexes

php_value default_charset UTF-8
AddDefaultCharset UTF-8

ErrorDocument 403 /
ErrorDocument 404 /'
        );
    }
}
