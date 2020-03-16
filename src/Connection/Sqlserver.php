<?php
namespace Mtchabok\Database\Connection;

use Mtchabok\Database\Connection;

class Sqlserver extends Connection
{
    protected $_driver = self::DRIVER_SQLSRV;
    protected $_server = 'localhost';
    protected $_database = '';
    protected $_user = 'sa';
    protected $_pass = '';
    protected $_tablePrefix = '';
}