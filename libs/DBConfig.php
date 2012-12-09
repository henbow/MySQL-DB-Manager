<?php
/**
 * DBConfig class. Class that containing configuration for MySQL server
 * @author Hendro Wibowo (hendro@tiwule.net)
 * @link http://www.github.com/
 * @since 05 November 2012
 * 
 */
 
class DBConfig
{
    public static $db_host = 'localhost';
    public static $db_user = 'root';
    public static $db_pass = '';
    public static $db_name = 'cdcol';
    public static $db_port = '';
    public static $db_pconnect = FALSE;
    
    // Available options is mysqli, mysql. Currently unused.
    public static $db_driver = 'mysqli';
}

/**
 * End of file DBConfig.php
 * Location: ./libs/DBConfig.php
 * 
 */