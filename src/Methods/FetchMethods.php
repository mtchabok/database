<?php
namespace Mtchabok\Database\Methods;
use Mtchabok\Database\Base\Query;
use Mtchabok\Database\Connection;
use Mtchabok\Database\Select;
use Mtchabok\Database\Statement;

trait FetchMethods
{
	/**
	 * @param null $fetch_style
	 * @param int $cursor_orientation
	 * @param int $cursor_offset
	 * @return mixed
	 */
	public function fetch($fetch_style = null, $cursor_orientation = null, $cursor_offset = null)
	{
		/** @var Select $this */
		if(!$this->isExecuted()) $this->execute();
		if($this->isExecuted()){
			/** @var Statement $st */
			$st = $this->_getStatement();
			return $st->fetch($fetch_style, $cursor_orientation, $cursor_offset);
		} return false;
	}


	/**
	 * @param string $class_name=null
	 * @param array|null $ctor_args=null
	 * @return mixed
	 */
	public function fetchObject($class_name = null, $ctor_args = null)
	{
		/** @var Select $this */
		if(!$this->isExecuted()) $this->execute();
		if($this->isExecuted()){
			/** @var Statement $st */
			$st = $this->_getStatement();
			return $st->fetchObject($class_name, $ctor_args);
		} return false;
	}

	/**
	 * @param null $fetch_style
	 * @param null $fetch_argument
	 * @param array $ctor_args
	 * @return array|false
	 */
	public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null) :array
	{
		/** @var Select $this */
		if(!$this->isExecuted()) $this->execute();
		if($this->isExecuted()){
			/** @var Statement $st */
			$st = $this->_getStatement();
			return $st->fetchAll($fetch_style, $fetch_argument, $ctor_args);
		} return false;
	}

	/**
	 * @param null $fetch_argument
	 * @param array $ctor_args
	 * @return array|false
	 */
	public function fetchAllArray($fetch_argument = null, $ctor_args = null) :array
	{ return $this->fetchAll(Connection::FETCH_ASSOC, $fetch_argument, $ctor_args); }

	/**
	 * @param null $fetch_argument
	 * @param array $ctor_args
	 * @return array|false
	 */
	public function fetchAllObject($fetch_argument = null, $ctor_args = null) :array
	{ return $this->fetchAll(Connection::FETCH_CLASS, $fetch_argument, $ctor_args); }
}