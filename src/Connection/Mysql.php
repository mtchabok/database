<?php
namespace Mtchabok\Database\Connection;

use Mtchabok\Database\Connection;

class Mysql extends Connection
{
    protected $_driver = self::DRIVER_MYSQL;
    protected $_server = 'localhost';
    protected $_database = '';
    protected $_user = 'root';
    protected $_tablePrefix = '';
    protected $_charset = self::CHARSET_UTF8;
}