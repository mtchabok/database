<?php
namespace Mtchabok\Database\Base;

/**
 * Class Conditions
 * @package Mtchabok\Database
 */
class Conditions extends Objects
{
	/** @var array */
	protected $_conditions = [];
	/** @var bool */
	protected $_and = true;






	/** @return bool */
	public function isAnd() :bool
	{ return true===$this->_and; }

	/**
	 * @param bool $and
	 * @return $this
	 */
	public function setAnd(bool $and)
	{ $this->_and = (bool) $and; return $this; }









	/** @return bool */
	public function hasConditions() :bool
	{ return !empty($this->_conditions); }

	/**
	 * @param string|array|int|float|Condition|Conditions $values
	 * @param string|null $operator=null
	 * @param string|null $fieldName=null
	 * @param bool $and=true
	 * @return $this|false
	 */
	public function addCondition($values, $operator = null, $fieldName = null, $and = true)
	{
		if(!$values instanceof Conditions && !$values instanceof Condition)
		{ $values = $this->getNewCondition()->set($values, $operator, $fieldName, $and); }
		if($values instanceof Conditions || $values instanceof Condition){
			$this->_conditions[] = $values;
			$values->setParent($this);
		}else return false;
		return $this;
	}







	/**
	 * @param bool $autoAddToCondition=false
	 * @return Conditions
	 */
	public function getNewConditions(bool $autoAddToCondition = false)
	{
		$conditions = new static();
		$conditions->setParent($this);
		if($autoAddToCondition) $this->addCondition($conditions);
		return $conditions;
	}

	/**
	 * @param bool $autoAddToCondition=false
	 * @return Condition
	 */
	public function getNewCondition(bool $autoAddToCondition = false)
	{
		$condition = new Condition();
		$condition->setParent($this);
		if($autoAddToCondition) $this->addCondition($condition);
		return $condition;
	}














	public function __construct(Objects $query = null)
	{
		if(null!==$query) $this->setParent($query);
	}

	protected function _onGetChildesParams(): array
	{
		$params = [];
		foreach ($this->_conditions as $condition){
			if($condition instanceof Conditions || $condition instanceof Condition)
				$params = array_merge($params, $condition->_getAllParams());
		}
		return $params;
	}

	protected function _onToSqlString(): string
	{
		$sql = '';
		foreach ($this->_conditions as $condition){
			if($condition instanceof Conditions && $condition->hasConditions()){
				$sql.= ($sql ?($condition->isAnd() ?' AND ' :' OR ') :'')."({$condition})";
			}elseif ($condition instanceof Condition){
				if($sqlCondition = $condition->toString())
					$sql.= ($sql ?($condition->isAnd() ?' AND ' :' OR ') :'').$sqlCondition;
			}elseif (is_string($condition) && $condition)
				$sql.= ($sql ?' AND ' :'')."{$condition}";
		} return $sql;
	}


}