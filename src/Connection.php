<?php
/**
 * Created by PhpStorm.
 * Project: mtchabok_database
 * User: mtchabok
 * Date: 2019-05-16
 * Time: 1:13 AM
 */

namespace Mtchabok\Database;
use Mtchabok\Database\Base\Query;
use PDOException, Exception;

/**
 * Class Connection
 * @package Mtchabok\Database
 *
 * @property-read string id
 */
class Connection extends \PDO
{
    CONST DRIVER_MYSQL  = 'mysql';
	CONST DRIVER_SQLSRV = 'sqlsrv';

	CONST CHARSET_UTF8  = 'utf8';

	private $_id            = '';

	protected $_driver      = '';
	protected $_server      = '';
	protected $_port        = 0;
	protected $_user        = '';
	protected $_pass        = null;
	protected $_database    = '';
	protected $_charset     = '';
	protected $_options     = [];
	protected $_tablePrefix = '';
	protected $_databaseArabic = false;


	private $_connectionActive = false;

	protected $_selectClassName = '\\Mtchabok\\Database\\Select';
	protected $_insertClassName = '\\Mtchabok\\Database\\Insert';
	protected $_updateClassName = '\\Mtchabok\\Database\\Update';
	protected $_deleteClassName = '\\Mtchabok\\Database\\Delete';









	/** @return bool */
	public function isActiveConnection() :bool
	{ return $this->_connectionActive; }








	/** @return bool */
	public function hasDriver() :bool
	{ return !empty($this->_driver); }

	/** @return string */
	public function getDriver() :string
	{ return (string) $this->_driver; }

	/**
	 * @param string $driver
	 * @return $this|false
	 */
	public function setDriver(string $driver)
	{
		if($this->isActiveConnection()) return false;
		$this->_driver = (string) $driver;
		return $this;
	}











	/** @return bool */
	public function hasServer() :bool
	{ return !empty($this->_server); }

	/** @return string */
	public function getServer() :string
	{ return (string) $this->_server; }

	/**
	 * @param string $server
	 * @return $this|false
	 */
	public function setServer(string $server)
	{
		if($this->isActiveConnection()) return false;
		$this->_server = (string) $server;
		return $this;
	}








	/** @return bool */
	public function hasPort() :bool
	{ return !empty($this->_port); }

	/** @return int|null */
	public function getPort()
	{ return $this->_port ?intval($this->_port) :null; }

	/**
	 * @param string|int|null $port
	 * @return $this|false
	 */
	public function setPort($port)
	{
		if($this->isActiveConnection()) return false;
		$this->_port = intval($port);
		return $this;
	}








	/** @return bool */
	public function hasUser() :bool
	{ return !empty($this->_user); }

	/**
	 * @param string $user
	 * @return bool
	 */
	public function equalUser(string $user) :bool
	{ return $this->_user==$user; }

	/**
	 * @param string $user
	 * @return $this|false
	 */
	public function setUser(string $user)
	{
		if($this->isActiveConnection()) return false;
		$this->_user = (string) $user;
		return $this;
	}












	/** @return bool */
	public function hasPass() :bool
	{ return null!==$this->_pass; }

	/**
	 * @param string|null $pass
	 * @return bool
	 */
	public function equalPass(string $pass) :bool
	{ return $this->_pass===$pass; }

	/**
	 * @param string|null $pass
	 * @return $this|false
	 */
	public function setPass(string $pass)
	{
		if($this->isActiveConnection()) return false;
		$this->_pass = null===$pass ?null :(string) $pass;
		return $this;
	}











	/** @return bool */
	public function hasDatabase() :bool
	{ return !empty($this->_database); }

	/** @return string */
	public function getDatabase() :string
	{ return (string) $this->_database; }

	/**
	 * @param string $database
	 * @return $this|false
	 */
	public function setDatabase(string $database)
	{
		if($this->isActiveConnection()) return false;
		$this->_database = (string) $database;
		return $this;
	}









	/** @return bool */
	public function hasCharset() :bool
	{ return !empty($this->_charset); }

	/** @return string */
	public function getCharset() :string
	{ return (string) $this->_charset; }

	/**
	 * @param string $charset
	 * @return $this|false
	 */
	public function setCharset(string $charset)
	{
		if($this->isActiveConnection()) return false;
		$this->_charset = (string) $charset;
		return $this;
	}









	/** @return bool */
	public function hasOptions() :bool
	{ return true; }

	/** @return array */
	public function getOptions() :array
	{
		if(!isset($this->_options[static::ATTR_ERRMODE]))
			$this->_options[static::ATTR_ERRMODE] = static::ERRMODE_EXCEPTION;
		switch ($this->getDriver()){
			case static::DRIVER_MYSQL:
				if($this->hasCharset() && !isset($this->_options[static::MYSQL_ATTR_INIT_COMMAND]))
					$this->_options[static::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES \"{$this->getCharset()}\"";
				break;
			case static::DRIVER_SQLSRV:
				if($this->hasCharset() && !isset($this->_options[static::SQLSRV_ATTR_ENCODING])){
					switch ($this->getCharset()){
						case static::CHARSET_UTF8:
							$this->_options[static::SQLSRV_ATTR_ENCODING] = static::SQLSRV_ENCODING_UTF8;
							break;
						default:
							$this->_options[static::SQLSRV_ATTR_ENCODING] = $this->getCharset();
							break;
					}
				}
				break;
		}
		return (array) $this->_options;
	}

	/**
	 * @param array $options
	 * @return $this|false
	 */
	public function setOptions(array $options)
	{
		if($this->isActiveConnection()) return false;
		$this->_options = (array) $options;
		return $this;
	}










	/** @return bool */
	public function hasTablePrefix() :bool
	{ return !empty($this->_tablePrefix); }

	/** @return string */
	public function getTablePrefix() :string
	{ return (string) $this->_tablePrefix; }

	/**
	 * @param string $tablePrefix
	 * @return $this|false
	 */
	public function setTablePrefix(string $tablePrefix)
	{
		if($this->isActiveConnection()) return false;
		$this->_tablePrefix = (string) $tablePrefix;
		return $this;
	}








	/** @return bool */
	public function isDatabaseArabic() :bool
	{ return (bool) $this->_databaseArabic; }

	/**
	 * @param bool $databaseArabic
	 * @return $this|false
	 */
	public function setDatabaseArabic(bool $databaseArabic)
	{
		if($this->isActiveConnection()) return false;
		$this->_databaseArabic = (bool) $databaseArabic;
		return $this;
	}









	/** @return string */
	public function getPdoDsn() :string
	{
		$dsn = '';
		switch ($this->getDriver()){
			case static::DRIVER_MYSQL:
				$dsn = array();
				$dsn[] = 'host='.$this->getServer();
				if($this->hasPort()) $dsn[] = 'port='.$this->getPort();
				if($this->hasDatabase()) $dsn[] = 'dbname='.$this->getDatabase();
				if($this->hasCharset()) $dsn[] = 'charset='.$this->getCharset();
				$dsn = 'mysql:'.implode(';', $dsn);
				break;
			case static::DRIVER_SQLSRV:
				$dsn = array();
				$dsn[] = 'server='.$this->getServer().($this->hasPort()?",{$this->getPort()}":'');
				if($this->hasDatabase()) $dsn[] = 'database='.$this->getDatabase();
				//if($this->hasCharset()) $dsn[] = 'charset='.$this->getCharset();
				$dsn = 'sqlsrv:'.implode(';', $dsn);
				break;
		}
		return $dsn;
	}

	/**
	 * @param string $dsn
	 * @return $this|false
	 */
	public function setPdoDsn(string $dsn)
	{
		if($this->isActiveConnection()) return false;
		$driver = substr($dsn, 0, strpos($dsn, ':'));
		if(false===$this->setDriver($driver)) return false;
		$result = [];
		if(false===preg_match_all('!'.'(?P<name>\w*)\=(?P<value>[^\;]*)?'.'!', substr($dsn, strlen($driver)+1), $result)) return false;
		$this->setServer('')->setPort(0)->setDatabase('')->setCharset('');
		for($i=0; !empty($result['name']) && $i<count($result['name']); $i++){
			switch (strtolower($result['name'][$i])){
				case 'host': case 'server':
					$portPos = strpos($result['value'][$i], ':');
					$this->setServer(false===$portPos ?$result['value'][$i] :substr($result['value'][$i], 0, $portPos));
					if(false!==$portPos)
						$this->setPort(substr($result['value'][$i], $portPos+1));
					break;
				case 'port':
					$this->setPort($result['value'][$i]);
					break;
				case 'dbname':case 'database':
					$this->setDatabase($result['value'][$i]);
					break;
				case 'charset':
					$this->setCharset($result['value'][$i]);
					break;
			}
		}
		return $this;
	}









	/**
	 * @return $this
	 * @throws PDOException
	 */
	public function connect()
	{
		if(!$this->isActiveConnection() && $this->id) {
			try {
				parent::__construct($this->getPdoDsn(), $this->_user, $this->_pass, $this->getOptions());
				$this->setAttribute(static::ATTR_STATEMENT_CLASS, ['\\Mtchabok\\Database\\Statement', [$this]]);
				$this->_connectionActive = true;
			} catch (PDOException $exception) {
				$this->_connectionActive = false;
				throw $exception;
			}
		}
		return $this;
	}




	/** @return bool */
	public function beginTransaction() :bool
	{ if(!$this->isActiveConnection()) $this->connect(); return parent::beginTransaction(); }

	/** @return bool */
	public function commit() :bool
	{ return $this->isActiveConnection() ?parent::commit() :false; }

	/** @return bool */
	public function rollBack() :bool
	{ return $this->isActiveConnection() ?parent::rollBack() :false; }

	/** @return bool */
	public function inTransaction() :bool
	{ return $this->isActiveConnection() ?parent::inTransaction() :false; }

	public function lastInsertId($name = null)
	{ return $this->isActiveConnection() ?parent::lastInsertId($name) :''; }











	/**
	 * @param int|float|string|array $string
	 * @param int $parameter_type
	 * @return array|string
	 */
	public function quote($string, $parameter_type = self::PARAM_STR)
	{
		if(!$this->isActiveConnection()) $this->connect();
		$groupMode = true;
		if(!is_array($string)){
			$groupMode = false;
			$string = [$string];
		}
		foreach ($string as &$v)
			$v = parent::quote($this->prepareParamValue($v), $parameter_type);
		return $groupMode ?$string :array_shift($string);
	}

	/**
	 * return quote table name
	 * @param string $name
	 * @param string $alias=null
	 * @return string
	 */
	public function quoteTable($name, $alias = null)
	{
		switch ($this->getDriver()){
			case static::DRIVER_MYSQL:
				$name = explode('.', $name);
				for($i=0; $i<count($name); $i++){
					$name[$i] = trim($name[$i], '`');
					if($this->hasTablePrefix() && $i+1==count($name))
						$name[$i] = "{$this->getTablePrefix()}{$name[$i]}";
					$name[$i] = "`{$name[$i]}`";
				}
				$name = implode('.', $name);
				if($alias) $name.= " AS {$this->quoteName($alias)}";
				break;
			case static::DRIVER_SQLSRV:
				$name = explode('.', $name);
				for($i=0; $i<count($name); $i++){
					$name[$i] = trim($name[$i], '[]"');
					if($this->hasTablePrefix() && $i+1==count($name))
						$name[$i] = "{$this->getTablePrefix()}{$name[$i]}";
					$name[$i] = "[{$name[$i]}]";
				}
				$name = implode('.', $name);
				if($alias) $name.= " AS {$this->quoteName($alias)}";
				break;
		}
		return $name;
	}

	/**
	 * return quote field name of table
	 * @param string $name
	 * @param string $alias=null
	 * @return string
	 */
	public function quoteName($name, $alias = null)
	{
		switch ($this->getDriver()){
			case static::DRIVER_MYSQL:
				$name = explode('.', $name);
				$fieldName = '`'.trim(array_pop($name), '`').'`';
				$name = ($name ?"{$this->quoteTable(implode('.', $name))}." :'').$fieldName;
				if($alias) $name.= " AS {$this->quoteName($alias)}";
				break;
			case static::DRIVER_SQLSRV:
				$name = explode('.', $name);
				$fieldName = '['.trim(array_pop($name), '[]"').']';
				$name = ($name ?"{$this->quoteTable(implode('.', $name))}." :'').$fieldName;
				if($alias) $name.= " AS {$this->quoteName($alias)}";
				break;
		}
		return $name;
	}








	/**
	 * @param string $fieldName
	 * @param int|float|string $value
	 * @return string
	 */
	public function getSqlIfNull($fieldName, $value) :string
	{
		$value = $this->quote($value);
		$return = '';
		switch ($this->getDriver()){
			case static::DRIVER_MYSQL:
				$return = "IFNULL({$fieldName}, $value)";
				break;
			case static::DRIVER_SQLSRV:
				$return = "ISNULL({$fieldName}, $value)";
				break;
		} return $return;
	}

	/**
	 * @param string $alias
	 * @return string
	 */
	public function getSqlAlias(string $alias) :string
	{
		switch ($this->getDriver()){
			case static::DRIVER_MYSQL:
				$alias = "AS {$this->quoteName($alias)}";
				break;
			case static::DRIVER_SQLSRV:
				$alias = "AS {$this->quoteName($alias)}";
				break;
		} return $alias;
	}










	/**
	 * @param string|array|Select $from=null
	 * @param array|string $fields=null
	 * @return false|Select
	 */
	public function newSelect($from = null, $fields = null)
	{
		/** @var Select $selectObj */
		if(!($selectObj = new $this->_selectClassName($from, $fields)) instanceof Select) return false;
		$selectObj->setConnection($this);
		return $selectObj;
	}

	/**
	 * @param string $tableName=null
	 * @param array|object $rows=null
	 * @param array $fields=null
	 * @return Insert|false
	 */
	public function newInsert(string $tableName = null, $rows = null, array $fields = null)
	{
		/** @var Insert $insertObj */
		if(!($insertObj = new $this->_insertClassName($tableName, $rows, $fields)) instanceof Insert) return false;
		$insertObj->setConnection($this);
		return $insertObj;
	}

	/**
	 * @param string $tableName = null
	 * @param string|array|object|Select $rows=null
	 * @param string|array $tableKeys=null
	 * @return Update|false
	 */
	public function newUpdate(string $tableName = null, $rows = null, $tableKeys = null)
	{
		/** @var Update $updateObj */
		if(!($updateObj = new $this->_updateClassName($tableName, $rows, $tableKeys)) instanceof Update) return false;
		$updateObj->setConnection($this);
		return $updateObj;
	}

	/**
	 * @param string $tableName=null
	 * @return bool|Delete
	 */
	public function newDelete(string $tableName = null)
	{
		/** @var Delete $deleteObj */
		if(!($deleteObj = new $this->_deleteClassName($tableName)) instanceof Delete) return false;
		$deleteObj->setConnection($this);
		return $deleteObj;
	}
















	/**
	 * @param string|Query $statement
	 * @param array $driver_options=[]
	 * @return Statement|false
	 */
	public function prepare($statement, $driver_options = null)
	{
		if(!$this->isActiveConnection()) $this->connect();
		$statement = $this->prepareQuery($statement);
		return parent::prepare($this->prepareQuery($statement), null===$driver_options ?[] :$driver_options);
	}

	/**
	 * execute any query without use params and return Dataset
	 * @param string|Query $statement
	 * @param int $arg1=null [$PDO::FETCH_COLUMN, $PDO::FETCH_CLASS, $PDO::FETCH_INTO]
	 * @param mixed $arg2=null
	 *          IF $arg1==$PDO::FETCH_COLUMN then $colno
	 *          IF $arg1==$PDO::FETCH_CLASS then $classname
	 *          IF $arg1==$PDO::FETCH_INTO then $object
	 * @param array $arg3=null
	 *          IF $arg1==$PDO::FETCH_CLASS then $ctorargs
	 * @return Statement|false
	 */
	public function query($statement, $arg1 = null, $arg2 = null, array $arg3 = null)
	{
		if(!$this->isActiveConnection()) $this->connect();
		$statement = $this->prepareQuery($statement);
		if(null!==$arg3)
			$statement = parent::query($statement, $arg1, $arg2, $arg3);
		elseif (null!==$arg2)
			$statement = parent::query($statement, $arg1, $arg2);
		elseif (null!==$arg1)
			$statement = parent::query($statement, $arg1);
		else
			$statement = parent::query($statement);
		return $statement;
	}

	/**
	 * execute INSERT|UPDATE|DELETE Query and return number of affected records
	 * @param string|Query $statement
	 * @return bool|int
	 */
	public function exec($statement)
	{
		if(!$this->isActiveConnection()) $this->connect();
		return parent::exec($this->prepareQuery($statement));
	}








	/**
	 * @param string|Query $query
	 * @return string
	 */
	public function prepareQuery($query)
	{
		$results = [];
//		$queryObj = $query instanceof Query ?$query :null;
		$query = (string) $query;
		/*if($queryObj && $queryObj->isQuotedParams() && $queryObj->hasParam()){
			foreach ($queryObj->getParams() as $name=>$value)
				$query = str_replace($name, $this->quote($value), $query);
		}*/
		if(false===preg_match_all('!'.'\$(?P<name>[TNV]{1})\[(?P<value>[^\[\]]*)\](([ ]*(AS|as)[ ]*)\[(?P<alias>[^\[\]]*)\])?'.'!', $query, $results)
			|| empty($results['name']))
			return $query;
		foreach ($results['name'] as $i=>$name){
			$findStr = $results[0][$i];
			$replaceStr = '';
			$value = &$results['value'][$i];
			$alias = &$results['alias'][$i];
			switch ($name){
				case 'T': $replaceStr = $this->quoteTable($value, $alias ?$alias :null); break;
				case 'N': $replaceStr = $this->quoteName($value, $alias ?$alias :null); break;
				case 'V':
					if(strpos($value, '|')!==false){
						$replaceStr = '('.implode(',', $this->quote(explode('|', $value))).')';
					}else
						$replaceStr = $this->quote($value);
					break;
			}
			$query = str_replace($findStr, $replaceStr, (string) $query);
		}
		return $query;
	}

	/**
	 * @param mixed $values
	 * @return mixed
	 */
	public function prepareParamValue($values)
	{
		if(is_string($values)){
			static $strReplace = ['۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4', '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9', '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4', '٥'=>'5', '٦'=>'6',
				'٧'=>'7', '٨'=>'8', '٩‎'=>'9'];
			if($this->isDatabaseArabic())
				$strReplace = array_merge($strReplace, ['ک'=>'ك', 'ی'=>'ي']);
			else
				$strReplace = array_merge($strReplace, ['ك'=>'ک', 'ي'=>'ی']);
			return str_replace(array_keys($strReplace), array_values($strReplace), $values);
		}elseif (is_array($values)){
			foreach ($values as $index=>$value)
				$values[$index] = $this->prepareParamValue($value);
			return $values;
		}elseif (is_object($values)){
			foreach (get_object_vars($values) as $name=>$value)
				$values->{$name} = $this->prepareParamValue($value);
			return $values;
		}else return $values;
	}

	/**
	 * @param mixed $values
	 * @return mixed
	 */
	public function prepareFetchValue($values)
	{
		if(is_string($values)){
			static $strReplace = ['ك'=>'ک', 'ي'=>'ی'];
			$values = str_replace(array_keys($strReplace), array_values($strReplace), $values);
		}elseif (is_array($values)){
			foreach ($values as $index=>$value)
				$values[$index] = $this->prepareFetchValue($value);
		}elseif (is_object($values)){
			foreach (get_object_vars($values) as $name=>$value)
				$values->{$name} = $this->prepareFetchValue($value);
		}
		return $values;
	}











	/** @return Connection */
	public function newThisConnection() :Connection
	{
		$dsn = $this->getPdoDsn();
		$username = $this->_user;
		$passwd = $this->_pass;
		$options = $this->getOptions();
		$connection = new static($dsn ?$dsn :null, $username ?$username :null, null!==$passwd ?$passwd :null, $options ?$options :null);
		if($this->hasCharset())
			$connection->setCharset($this->getCharset());
		if($this->hasTablePrefix())
			$connection->setTablePrefix($this->getTablePrefix());
		if($this->isDatabaseArabic())
			$connection->setDatabaseArabic(true);
		return $connection;
	}



	/**
	 * @param string|null $dsn=null
	 * @param string $username=null
	 * @param string $passwd=null
	 * @param array $options=null
	 * @return Connection
	 */
	public static function newConnection(string $dsn = null, string $username = null, string $passwd = null, array $options = null) :Connection
	{ return new static($dsn, $username, $passwd, $options); }






	/**
	 * Connection constructor.
	 * @param string|null $dsn=null
	 * @param string $username=null
	 * @param string $passwd=null
	 * @param array $options=null
	 */
	public function __construct(string $dsn = null, string $username = null, string $passwd = null, array $options = null)
	{
		if(null!==$dsn) $this->setPdoDsn($dsn);
		if(null!==$username) $this->setUser($username);
		if(null!==$passwd) $this->setPass($passwd);
		if(null!==$options) $this->setOptions($options);
	}

	public function __get($name)
	{
		switch ($name){
			case 'id':
			    if(empty($this->_id)) $this->_id = spl_object_id($this);
			    return $this->_id;
			default: return null;
		}
	}

	public function __set($name, $value)
	{}

	public function __isset($name)
	{ return in_array($name, ['id']); }

	public function __unset($name)
	{}

    public function __toString() :string
    { return (string) $this->id; }


    /**
	 * @throws Exception
	 */
	public function __clone()
	{
		throw new Exception('Connection Class could not create clone object.');
	}


}