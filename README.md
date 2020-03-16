# PHP PDO Database Control
PHP PDO Database Class for handling Databases.

- unlimited managment connection to databases
- objective query control
- fast execute query
- php pdo support
- support add/edit variable`s in queries
- subqueries supported
- pdo (mysql, sqlsrv) support
- auto generate query
- database/table/field name control 

Installation
------------

This package is listed on [Packagist](https://packagist.org/packages/mtchabok/database).

```
composer require mtchabok/database
```

How To Usage
------------

#### Create Connection Object ####
```php
use \Mtchabok\Database\Connection;

$connection = new Connection('mysql:host=localhost;dbname=test;charset=utf8', 'root','');

$connection = Connection::newConnection('mysql:host=localhost;dbname=test;charset=utf8', 'root','');

$connection = (new Connection())
	->setDriver(Connection::DRIVER_MYSQL)
	->setServer('localhost')
	->setDatabase('test')
	->setUser('root')
	->setPass('')
	->setCharset(Connection::CHARSET_UTF8)
;

class my_connection extends Connection
{
	protected $_driver = Connection::DRIVER_MYSQL;
	protected $_server = 'localhost';
	protected $_database = 'test';
	protected $_user = 'root';
	protected $_charset = Connection::CHARSET_UTF8;
}
$connection = new my_connection();

$connection = new Connection\Mysql();
$connection->setDatabase('test');

```

#### Create Select/Insert/Update/Delete Query Object ####
```php
use Mtchabok\Database\Select;
use Mtchabok\Database\Insert;
use Mtchabok\Database\Update;
use Mtchabok\Database\Delete;

$qSelect = $connection->newSelect('person');
$qInsert = $connection->newInsert('person');
$qUpdate = $connection->newUpdate('person');
$qDelete = $connection->newDelete('person');

$qSelect = (new Select('person'))->setConnection($connection);
$qInsert = (new Insert('person'))->setConnection($connection);
$qUpdate = (new Update('person'))->setConnection($connection);
$qDelete = (new Delete('person'))->setConnection($connection);

$qSelect = (Select::newSelect('person'))->setConnection($connection);
```

#### Execute Query ####
```php
$qSelect->setField(['id', 'name', 'mobile'])
	->addWhere('%mohammad%', 'LIKE', 'name')
	->setOrderBy('id', 'DESC')
;
$result = $qSelect->fetchAllObject(); // return array of objects
```

#### For More Usage Documentation, Use This Database Package By IDE ####