<?php
namespace Mtchabok\Database\Methods;
use Mtchabok\Database\Delete;
use Mtchabok\Database\Select;
/**
 * Trait FromMethods
 * @package Mtchabok\Database
 */
trait FromMethods
{
	/** @var array */
	protected $_from = [];




	/** @return bool */
	public function hasFrom() :bool
	{ return !empty($this->_from); }

	/** @return array */
	public function getFrom() :array
	{ return $this->_from; }

	/**
	 * @param string|array|Select $from
	 * @param string|null $alias=null
	 * @return $this|false
	 */
	public function setFrom($from, $alias = null)
	{
		$this->_clearCache();
		if(!is_array($from)) $from = null===$alias ?[$from] :[$alias=>$from];
		foreach ($from as $alias=>$item){
			if(!is_numeric($alias)){
				if(!$this->addFrom($item, $alias)) return false;
			}elseif(!$this->addFrom($item)) return false;
		} return $this;
	}

	/**
	 * @param string|Select $from
	 * @param string|null $alias=null
	 * @return $this|false
	 */
	public function addFrom($from, string $alias = null)
	{
		$this->_clearCache();
		if(is_string($from) || $from instanceof Select) {
			if(null===$alias) $this->_from[] = $from;
			elseif (is_string($alias) && $alias) $this->_from[$alias] = $from;
			else return false;
			if($from instanceof Select) $from->setParent($this);
			return $this;
		}else return false;
	}




	/** @return array */
	protected function _fromParams() :array
	{
		$params = [];
		if($this->hasFrom() && ($from = $this->getFrom())){
			foreach ($from as $item){
				if($item instanceof Select)
					$params = array_merge($params, $item->_getAllParams());
			}
		} return $params;
	}

	/** @return string */
	protected function _fromToString() :string
	{
		$sql = '';
		if($this->hasFrom()){
			$from = [];
			$alias = [];
			foreach ($this->getFrom() as $key=>$item){
				if ($item instanceof Select)
					$item = $item->toString();
				if(is_string($item) && $item){
					if(!is_numeric($key)){
					    $item.= ' '.$this->getConnection()->getSqlAlias($key);
					    $alias[] = $this->QN($key);
                    } $from[] = $item;
				}
			}
			if($from) $sql = 'FROM '.implode(',', $from);
			if($this instanceof Delete && ($alias || $this->hasJoin()))
			    $sql = implode(',', $alias ?$alias :$from)." {$sql}";
		} return $sql;
	}
}