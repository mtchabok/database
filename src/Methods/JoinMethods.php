<?php
namespace Mtchabok\Database\Methods;
use Mtchabok\Database\Base\Query;
use Mtchabok\Database\Select, Mtchabok\Database\Base\Join, Mtchabok\Database\Base\Conditions;
/**
 * Trait JoinMethods
 * @package Mtchabok\Database
 */
trait JoinMethods
{
	/** @var array */
	protected $_joins = [];




	/** @return bool */
	public function hasJoin() :bool
	{ return !empty($this->_joins); }

	/** @return array */
	public function getJoins() :array
	{ return $this->_joins; }

	/** @return int */
	public function getNumJoins() :int
	{ return count($this->_joins); }

	/**
	 * @param bool $addToJoin=false
	 * @return Join
	 */
	public function getNewJoin(bool $addToJoin = false) :Join
	{
		$join = new Join();
		/** @var Query $this */
		$join->setParent($this);
		if($addToJoin) $this->addJoin($join);
		return $join;
	}

	/**
	 * @param string $type
	 * @param string|Select $table
	 * @param string|Conditions $on
	 * @return $this
	 */
	public function addJoin(string $type=null, $table=null, $on=null)
	{
		$this->_clearCache();
		$join = new Join();
		$this->_joins[] = $join;
		/** @var Query $this */
		$join->setParent($this);
		$join->set($type, $table, $on);
		return $this;
	}





	/** @return array */
	protected function _joinParams() :array
	{
		$params = [];
		if($this->hasJoin()){
			foreach ($this->getJoins() as $join){
				if ($join instanceof Join)
					$params = array_merge($params, $join->getParams());
			}
		} return $params;
	}

	/** @return string */
	protected function _joinToString() :string
	{
		$sql = [];
		if($this->hasJoin()){
			foreach ($this->getJoins() as $join){
				if(is_string($join))
					$sql[] = $join;
				elseif ($join instanceof Join){
					$sql[] = (string) $join;
				}
			}
		} return implode(' ', $sql);
	}
}