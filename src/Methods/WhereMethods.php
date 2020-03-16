<?php
namespace Mtchabok\Database\Methods;
use Mtchabok\Database\Base\Conditions, Mtchabok\Database\Base\Condition;
use Mtchabok\Database\Base\Query;

/**
 * Trait WhereMethods
 * @package Mtchabok\Database
 *
 *
 */
trait WhereMethods
{
	/** @var string|Conditions */
	protected $_where = '';


	/** @return bool */
	public function hasWhere() :bool
	{ return is_string($this->_where) ?!empty($this->_where) :($this->_where instanceof Conditions ?$this->_where->hasConditions() :false); }

	/** @return bool */
	public function isWhereConditions() :bool
	{ return !empty($this->_where) && $this->_where instanceof Conditions; }

	/** @return Conditions|string */
	public function getWhere()
	{ return $this->_where; }

	/**
	 * @param bool $addToWhere=false
	 * @return Conditions
	 */
	public function getNewWhereConditions(bool $addToWhere = false)
	{
		$conditions = new Conditions();
		/** @var Query $this */
		$conditions->setParent($this);
		if($addToWhere) $this->addWhere($conditions);
		return $conditions;
	}

	/**
	 * @param bool $addToWhere
	 * @return Condition
	 */
	public function getNewWhereCondition(bool $addToWhere = false)
	{
		$condition = new Condition();
		/** @var Query $this */
		$condition->setParent($this);
		if($addToWhere) $this->addWhere($condition);
		return $condition;
	}

	/**
	 * @param Conditions|string $whereString
	 * @return $this
	 */
	public function setWhere($whereString)
	{
		if(is_string($whereString) || $whereString instanceof Conditions) {
			$this->_clearCache();
			$this->_where = $whereString;
			if($this->_where instanceof Conditions)
				$this->_where->setParent($this);
		} return $this;
	}

	/**
	 * @param string|array|int|float|Condition|Conditions $values
	 * @param string|null $operator=null
	 * @param string|null $fieldName=null
	 * @param bool $and=true
	 * @return $this
	 */
	public function addWhere($values, $operator = null, $fieldName = null, $and = true)
	{
		$this->_clearCache();
		if(!$this->_where instanceof Conditions){
			$where = new Conditions();
			/** @var Query $this */
			$where->setParent($this);
			if(is_string($this->_where) && $this->_where)
				$where->addCondition($this->_where);
			$this->_where = $where;
		}
		$this->_where->addCondition($values, $operator, $fieldName, $and);
		return $this;
	}






	/** @return array */
	protected function _whereParams() :array
	{
		$params = [];
		if($this->isWhereConditions()){
			$params = $this->getWhere()->_getAllParams();
		} return $params;
	}

	/** @return string */
	protected function _whereToString() :string
	{
		$sql = '';
		if($this->hasWhere()){
			$where = $this->getWhere();
			if(is_string($where) && $where)
				$sql.= "WHERE {$where}";
			elseif ($where instanceof Conditions && $where->hasConditions())
				$sql.= "WHERE {$where}";
		}
		return $sql;
	}
}