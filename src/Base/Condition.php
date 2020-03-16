<?php
namespace Mtchabok\Database\Base;
use Mtchabok\Database\Connection;
/**
 * Class WhereCondition
 * @package Mtchabok\Database
 */
class Condition extends Objects
{
	/** @var string */
	protected $_fieldName = '';
	/** @var null|int|float|string */
	protected $_fieldNameIfNull;
	/** @var string */
	protected $_operator = '';
	/** @var bool */
	protected $_operatorNot = false;
	/** @var null|string|int|float|array */
	protected $_values;
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
	{ $this->_clearCache(); $this->_and = (bool) $and; return $this; }










	/** @return bool */
	public function hasFieldName() :bool
	{ return !empty($this->_fieldName); }

	/** @return string */
	public function getFieldName()
	{ return $this->_fieldName; }

	/**
	 * @param string $fieldName
	 * @param null|int|float|string $ifNull=null
	 * @return $this
	 */
	public function setFieldName(string $fieldName, $ifNull = null)
	{
		$this->_clearCache();
		$this->_fieldName = (string) $fieldName;
		$this->_fieldNameIfNull = $ifNull;
		return $this;
	}











	/** @return bool */
	public function hasOperator() :bool
	{ return !empty($this->_operator); }

	/** @return bool */
	public function isNotOperator() :bool
	{ return $this->_operatorNot; }

	/** @return string */
	public function getOperator() :string
	{ return $this->_operator; }

	/**
	 * @param string $operator
	 * @param bool $not=false
	 * @return $this
	 */
	public function setOperator(string $operator, bool $not = false)
	{
		$this->_clearCache();
		if(is_string($operator) && substr(strtoupper($operator), 0, 4)=='NOT '){
			$operator = substr($operator, 4);
			$not = true;
		}
		$this->_operator = $operator;
		$this->_operatorNot = (bool) $not;
		return $this;
	}












	/** @return bool */
	public function hasValues() :bool
	{ return null!==$this->_values; }

	/** @return null|string|int|float|array */
	public function getValues()
	{ return $this->_values; }

	/**
	 * @param string|int|float|array $values
	 * @return $this
	 */
	public function setValues($values)
	{
		$this->_clearCache();
		$this->_values = $values;
		return $this;
	}









	/**
	 * @param string|array $values
	 * @param string|bool|null $operator
	 * @param string|null $fieldName
	 * @param bool $and
	 * @return $this
	 */
	public function set($values, $operator = null, $fieldName = null, bool $and = true)
	{
		$this->_clearCache();
		$this->setValues($values);
		if(is_bool($operator)){
			$and = $operator;
			$operator = null;
		}elseif (is_bool($fieldName)){
			$and = $fieldName;
			$fieldName = null;
		}
		$this->setAnd($and);
		if(null!==$operator)
			$this->setOperator($operator);
		else{ $this->_operator = ''; $this->_operatorNot = false; }
		if(null!==$fieldName)
			$this->setFieldName($fieldName);
		else{ $this->_fieldName = ''; $this->_fieldNameIfNull = null; }
		return $this;
	}









	/**
	 * Condition constructor.
	 * @param string|array|null $values
	 * @param string|bool|null $operator
	 * @param string|null $fieldName
	 * @param bool $and
	 */
	public function __construct($values = null, $operator = null, $fieldName = null, bool $and = true)
	{ if(null!==$values) $this->set($values, $operator, $fieldName, $and); }

	protected function _onGetChildesParams(): array
	{ return []; }

	protected function _onToSqlString(): string
	{
		$sql = '';
		$fieldName = '';
		if($this->_fieldName){
			if(null!==$this->_fieldNameIfNull && $this->hasConnection()){
				$fieldName = $this->getConnection()->getSqlIfNull($this->_fieldName, $this->_fieldNameIfNull);
			}else $fieldName = $this->_fieldName;
		}
		$values = '';
		if($this->hasValues()){
			$values = $this->getValues();
			if(is_int($values) || is_numeric($values))
				$values = $this->QV($values, Connection::PARAM_INT);
			elseif ((is_string($values) && $this->_operator) || is_numeric($values))
				$values = $this->P($values);
			elseif(is_array($values))
//				$values = '('.implode(',', $this->QV($values)).')';
				$values = $this->P($values);
			elseif ($values instanceof Query)
				$values = "({$values})";
			elseif($this->_operator)
				$values = $this->QV((string) $values);
		}
		switch (strtoupper($this->_operator)){
			case '=':case '>':case '>=':case '<':case '<=':
				$sql = "{$fieldName} {$this->_operator} {$values}";
				break;
			case '!=': case '<>':
				$sql = "{$fieldName} != {$values}";
				break;
			case 'LIKE':
				$sql = $fieldName . ($this->_operatorNot ?' NOT' :'') . ' LIKE ' . $values;
				break;
			case 'IN':
				$sql = $fieldName . ($this->_operatorNot ?' NOT' :'') . ' IN ' . $values;
				break;
			case 'EXISTS':
				$sql = ($this->_operatorNot ?'NOT ' :'') . 'EXISTS ' . $values;
				break;
			case '':
				if($values){
					if($this->_operatorNot)
						$sql = "NOT ({$values})";
					else
						$sql = $values;
				}elseif ($fieldName){
					$sql = $fieldName;
				}
				break;
			default:
				$sql = "{$fieldName} {$this->_operator} {$values}";
		}
		return $sql;
	}
}