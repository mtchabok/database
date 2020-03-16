<?php
namespace Mtchabok\Database;
use PDO,PDOStatement;

/**
 * Class Statement
 * @package Mtchabok\Database
 */
class Statement extends PDOStatement
{
	/** @var Connection */
	private $_connection;
	/** @var bool */
	private $_executed = false;



	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @param int $data_type
	 * @return bool
	 */
	public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR) :bool
	{
		$this->_executed = false;
		return parent::bindValue($parameter, $this->_connection->prepareParamValue($value), $data_type);
	}

	/**
	 * @param string $parameter
	 * @param mixed $value
	 * @param int $data_type
	 * @param null $length
	 * @param null $driver_options
	 * @return bool
	 */
	public function bindParam($parameter, &$value, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null) :bool
	{
		$this->_executed = false;
		return parent::bindValue($parameter, $this->_connection->prepareParamValue($value), $data_type);
	}






	/** @return int */
	public function rowCount() :int
	{
		if(!$this->_executed) $this->execute();
		return parent::rowCount();
	}










    /**
	 * @param null $fetch_style
	 * @param int $cursor_orientation
	 * @param int $cursor_offset
	 * @return mixed
	 */
	public function fetch($fetch_style = null, $cursor_orientation = null, $cursor_offset = null)
	{
		if(!$this->_executed) $this->execute();
		$result = null===$fetch_style ?parent::fetch()
			:(null===$cursor_orientation ?parent::fetch($fetch_style)
			:(null===$cursor_offset ?parent::fetch($fetch_style, $cursor_orientation)
				:parent::fetch($fetch_style, $cursor_orientation, $cursor_offset)));
		return $this->_connection->prepareFetchValue($result);
	}


	/**
	 * @param string $class_name=null
	 * @param array|null $ctor_args=null
	 * @return mixed
	 */
	public function fetchObject($class_name = null, $ctor_args = null)
	{
		if(!$this->_executed) $this->execute();
		$result = null===$class_name ?parent::fetchObject()
			:(null===$ctor_args ?parent::fetchObject($class_name)
			:parent::fetchObject($class_name, $ctor_args));
		return $this->_connection->prepareFetchValue($result);
	}

	/**
	 * @param null $fetch_style
	 * @param null $fetch_argument
	 * @param array $ctor_args
	 * @return array
	 */
	public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null) :array
	{
		if(!$this->_executed) $this->execute();
		$result = null===$fetch_style ?parent::fetchAll()
			:(null===$fetch_argument ?parent::fetchAll($fetch_style)
			:(null===$ctor_args ?parent::fetchAll($fetch_style, $fetch_argument)
				:parent::fetchAll($fetch_style, $fetch_argument, $ctor_args)));
		return $this->_connection->prepareFetchValue($result);
	}

    /**
     * @param null $fetch_argument
     * @param array $ctor_args
     * @return array
     */
    public function fetchAllArray($fetch_argument = null, $ctor_args = null) :array
    { return $this->fetchAll(Connection::FETCH_ASSOC, $fetch_argument, $ctor_args); }

    /**
     * @param null $fetch_argument
     * @param array $ctor_args
     * @return array
     */
    public function fetchAllObject($fetch_argument = null, $ctor_args = null) :array
    { return $this->fetchAll(Connection::FETCH_CLASS, $fetch_argument, $ctor_args); }







	/**
	 * @param mixed $input_parameters
	 * @return bool
	 */
	public function execute($input_parameters = null)
	{
	    $this->_executed = is_null($input_parameters) ?parent::execute() :parent::execute($this->_connection->prepareParamValue($input_parameters));
		return $this->_executed;
	}





	protected function __construct(Connection $connection)
	{ $this->_connection = $connection; }

}