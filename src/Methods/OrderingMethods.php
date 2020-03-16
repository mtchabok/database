<?php
namespace Mtchabok\Database\Methods;

use Mtchabok\Database\Base\Query;

trait OrderingMethods
{
	/** @var array */
	protected $_ordering = [];


	/** @return bool */
	public function hasOrderBy() :bool
	{ return !empty($this->_ordering); }

	/** @return array */
	public function getOrderBy() :array
	{ return $this->_ordering; }


	/**
	 * @param array|string $by
	 * @param string $direction
	 * @return $this|false
	 */
	public function setOrderBy($by, $direction = '')
	{
		/** @var Query $this */
		$this->_clearCache();
		$this->_ordering = [];
		$bys = is_array($by) ?$by :[(string)$by=>(string)$direction];
		foreach ($bys as $by=>$dir){
			if(empty($by) || is_int($by)){ $by = $dir; $dir = $direction; }
			if(is_string($by) && !empty($by)){
				$this->_ordering[$by] = in_array(strtoupper($direction),['ASC','DESC']) ?strtoupper($direction) :'';
			}else return false;
		}
		return $this;
	}

	/**
	 * @param string $by
	 * @param string $direction
	 * @return $this|false
	 */
	public function addOrderBy(string $by, string $direction = '')
	{
		/** @var Query $this */
		$this->_clearCache();
		if(is_string($by) && !empty($by)){
			$this->_ordering[$by] = in_array(strtoupper($direction),['ASC','DESC']) ?strtoupper($direction) :'';
			return $this;
		}else return false;
	}



	/** @return string */
	protected function _orderingToString() :string
	{
		$sql = '';
		if($this->hasOrderBy()){
			$orderBys = [];
			foreach ($this->getOrderBy() as $by=>$dir)
				$orderBys[] = $by.($dir ?" {$dir}" :'');
			if($orderBys)
				$sql = 'ORDER BY '.implode(',', $orderBys);
		} return $sql;
	}
}