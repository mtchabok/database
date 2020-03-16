<?php
/**
 * Created by PhpStorm.
 * Project: mtchabok_database
 * User: mtchabok
 * Date: 2019-05-19
 * Time: 11:05 PM
 */

namespace Mtchabok\Database\Base;
use Mtchabok\Database\Connection;
use Mtchabok\Database\Statement;

abstract class Query extends Objects
{
	/** @var Connection */
	private $_connection;
	/** @var Statement */
	private $_statement;
	/** @var bool */
	private $_executed;







	/** @return bool */
	public function hasConnection() :bool
	{ return $this->hasParent() ?$this->getParent()->hasConnection() :!empty($this->_connection); }

	/** @return Connection|null */
	public function getConnection()
	{ return $this->hasParent() ?$this->getParent()->getConnection() :$this->_connection; }

	/**
	 * @param Connection $connection
	 * @return $this
	 */
	public function setConnection(Connection $connection)
	{
		if($this->hasParent())
			$this->getFirstParentQuery()->setConnection($connection);
		elseif(null===$this->_connection && $connection)
			$this->_connection = $connection;
		return $this;
	}


	/** @return Statement */
	protected function _getStatement()
	{ return $this->_statement; }




	/** @return bool */
	public function isExecuted() :bool
	{ return (bool) ($this->hasParent() ?$this->getParent()->isExecuted() :$this->_executed); }


	/** @return bool */
	public function execute() :bool
	{
		$this->_executed = false;
		if(!$this->hasParent()) {
			$connection = $this->getConnection();
			if (!$connection->isActiveConnection()) $connection->connect();
			$this->_statement = $connection->prepare($this);
			$params = $this->_getAllParams();
			if ($params && !$this->isQuotedParams())
				$this->_executed = (bool)$this->_statement->execute($params);
			else
				$this->_executed = (bool)$this->_statement->execute();
		} return $this->_executed;
	}


}