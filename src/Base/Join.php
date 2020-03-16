<?php
namespace Mtchabok\Database\Base;
use Mtchabok\Database\Select;
/**
 * Class Join
 * @package Mtchabok\Database
 */
class Join extends Objects
{
	/** @var string */
	protected $_type = 'LEFT';
	/** @var string|Select */
	protected $_table = '';
	/** @var string */
	protected $_tableAlias = '';
	/** @var string|Conditions */
	protected $_on;








	/** @return string */
	public function getType() :string
	{ return (string) $this->_type; }

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType(string $type)
	{
		if($this->_type!=($type=strtoupper($type))){
			$this->_clearCache();
			$this->_type = $type;
		} return $this;
	}







	/** @return bool */
	public function hasTable() :bool
	{ return !empty($this->_table); }

	/** @return string|Select */
	public function getTable()
	{ return $this->_table; }

	/**
	 * @param string|Select $table
	 * @param string|null $alias=null
	 * @return $this
	 */
	public function setTable($table, string $alias = null)
	{
		$this->_clearCache();
		$this->_table = ($table instanceof Select || is_string($table)) ?$table :'';
		$this->_tableAlias = (string) $alias;
		return $this;
	}







	/** @return bool */
	public function hasOn() :bool
	{ return is_string($this->_on) ?!empty($this->_on) :($this->_on instanceof Conditions ?$this->_on->hasConditions() :false); }

	/** @return bool */
	public function isOnConditions() :bool
	{ return !empty($this->_on) && $this->_on instanceof Conditions; }

	/** @return Conditions|string */
	public function getOn()
	{ return $this->_on; }

	/**
	 * @param bool $addToOn=false
	 * @return Conditions
	 */
	public function getNewOnConditions(bool $addToOn = false)
	{
		$conditions = new Conditions();
		$conditions->setParent($this);
		if($addToOn) $this->addOn($conditions);
		return $conditions;
	}

	/**
	 * @param bool $addToOn
	 * @return Condition
	 */
	public function getNewOnCondition(bool $addToOn = false)
	{
		$condition = new Condition();
		$condition->setParent($this);
		if($addToOn) $this->addOn($condition);
		return $condition;
	}

	/**
	 * @param string|Conditions $on
	 * @return $this
	 */
	public function setOn($on)
	{
		if(is_string($on) || $on instanceof Conditions) {
			$this->_clearCache();
			$this->_on = $on;
			if($this->_on instanceof Conditions)
				$this->_on->setParent($this);
		} return $this;
	}

	/**
	 * @param string|array|int|float|Condition|Conditions $values
	 * @param string|null $operator=null
	 * @param string|null $fieldName=null
	 * @param bool $and=true
	 * @return $this
	 */
	public function addOn($values, $operator = null, $fieldName = null, $and = true)
	{
		$this->_clearCache();
		if(!$this->_on instanceof Conditions){
			$on = new Conditions();
			$on->setParent($this);
			if(is_string($this->_on) && $this->_on)
				$on->addCondition($this->_on);
			$this->_on = $on;
		} $this->_on->addCondition($values, $operator, $fieldName, $and);
		return $this;
	}








	/**
	 * @param string $type
	 * @param string|Select $table
	 * @param string|Conditions $on
	 * @return $this
	 */
	public function set(string $type, $table, $on)
	{
		$this->_clearCache();
		$this->setType($type);
		$this->setTable($table);
		$this->setOn($on);
		return $this;
	}








	/**
	 * @param string $type
	 * @param string|Select $table
	 * @param string|Conditions $on
	 */
	public function __construct(string $type=null, $table=null, $on=null)
	{ if(null!==$type && null!==$table && null!==$on) $this->set($type, $table, $on); }

	protected function _onGetChildesParams(): array
	{
		$params = [];
		if($this->_table instanceof Select && $this->_table->hasParam())
			$params = $this->_table->_getAllParams();
		if($this->isOnConditions() && $this->_on->hasParam())
			$params = array_merge($params, $this->_on->_getAllParams());
		return $params;
	}

	protected function _onToSqlString(): string
	{
		$type = strtoupper(trim($this->_type));
		if(substr($type, -1, 4)!='JOIN')
			$type.= ' JOIN';
		$sql = $type;
		if(is_string($this->_table))
			$sql.= " {$this->_table}";
		elseif ($this->_table instanceof Select)
			$sql.= " ({$this->_table})";
		if($this->_tableAlias)
			$sql.= ' '.$this->getConnection()->getSqlAlias($this->_tableAlias);
		if($this->hasOn()){
			$on = $this->getOn();
			if(is_string($on) && $on)
				$sql.= " ON {$on}";
			elseif ($on instanceof Conditions && $on->hasConditions())
				$sql.= " ON {$on}";
		} return $sql;
	}

}