<?php
namespace Mtchabok\Database\Methods;
use Mtchabok\Database\Select;
/**
 * Trait FieldMethods
 * @package Mtchabok\Database
 */
trait FieldMethods
{
	/** @var array */
	protected $_fields = [];
	/** @var bool */
	protected $_allowFieldAlias = true;


	/** @return bool */
	public function hasField() :bool
	{ return !empty($this->_fields); }

	/**
	 * @param string $fieldName
	 * @return bool
	 */
	public function existField(string $fieldName) :bool
	{ return in_array($fieldName, $this->_fields); }

	/** @return array */
	public function getField() :array
	{ return $this->_fields; }

    /** @return int */
    public function getNumFields() :int
    { return count($this->_fields); }

	/**
	 * @param string|Select|array $field
	 * @param string|null $alias=null
	 * @return $this|false
	 */
	public function setField($field, $alias = null)
	{
		$this->_clearCache();
		if(!is_array($field)) $field = (!$this->_allowFieldAlias || null===$alias) ?[$field] :[$alias=>$field];
		foreach ($field as $alias=>$item){
			if($this->_allowFieldAlias && !is_numeric($alias)){
				if(!$this->addField($item, $alias)) return false;
			}elseif(!$this->addField($item)) return false;
		} return $this;
	}

	/**
	 * @param string|Select $field
	 * @param string|null $alias=null
	 * @return $this|false
	 */
	public function addField($field, string $alias = null)
	{
		$this->_clearCache();
		if(is_string($field) || $field instanceof Select) {
			if(!$this->_allowFieldAlias || null===$alias) $this->_fields[] = $field;
			elseif (is_string($alias) && $alias) $this->_fields[$alias] = $field;
			else return false;
			if($field instanceof Select) $field->setParent($this);
			return $this;
		}else return false;
	}



	/** @return array */
	protected function _fieldParams() :array
	{
		$params = [];
		if($this->hasField() && ($field = $this->getField())){
			foreach ($field as $item){
				if($item instanceof Select && !$item->isQuotedParams())
					$params = array_merge($params, $item->_getAllParams());
			}
		} return $params;
	}


	/** @return string */
	protected function _fieldToString() :string
	{
		$sql = '';
		if($this->hasField()){
			$field = [];
			foreach ($this->getField() as $key=>$item){
				if ($item instanceof Select)
					$item = "({$item->toString()})";
				if(is_string($item) && $item){
					if($this->_allowFieldAlias && !is_numeric($key)) $item.= ' '.$this->getConnection()->getSqlAlias($key);
					$field[] = $item;
				}
			}
			if($field) $sql = implode(',', $field);
		} return $sql;
	}
}