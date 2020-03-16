<?php
/**
 * Created by PhpStorm.
 * Project: mtchabok_database
 * User: mtchabok
 * Date: 2019-06-08
 * Time: 10:21 PM
 */

namespace Mtchabok\Database\Base;
use Mtchabok\Database\Connection;
/**
 * Class Objects
 * @package Mtchabok\Database
 */
abstract class Objects
{
	/** @var Query */
	private $_parent;
	/** @var array */
	private $_parameters = [];
	/** @var bool */
	protected $_quotedParams = false;

	/** @var int */
	private $_cacheQueryStringCreated = 0;
	/** @var string */
	private $_cacheQueryString = '';
	/** @var int */
	private $_cacheParametersCreated = 0;
	/** @var array */
	private $_cacheParameters = [];










	/** @return bool */
	public function hasParent() :bool
	{ return !empty($this->_parent); }

	/** @return Query|Objects */
	public function getParent()
	{ return $this->_parent; }

	/** @return Query|null */
	public function getFirstParentQuery()
	{ return $this->hasParent() ?$this->_parent->getFirstParentQuery() :($this instanceof Query ?$this :null); }

	/**
	 * @param Objects $parent
	 * @return $this
	 */
	public function setParent(Objects $parent)
	{
		$this->_parent = $parent;
		$this->_clearCache();
		return $this;
	}









	/** @return bool */
	public function hasConnection() :bool
	{ return $this->_parent ?$this->_parent->hasConnection() :false; }

	/** @return Connection|null */
	public function getConnection()
	{ return $this->_parent ?$this->_parent->getConnection() :null; }







	/** @return bool */
	public function isExecuted() :bool
	{ return $this->hasParent() ?$this->getParent()->isExecuted() :false; }






	/** @return bool */
	public function isQuotedParams() :bool
	{ return (bool) $this->_quotedParams; }

	/**
	 * @param bool $quoted
	 * @return $this
	 */
	public function setQuotedParams(bool $quoted)
	{
		$this->_clearCache();
		$this->_quotedParams = (bool) $quoted;
		return $this;
	}








	/** @return bool */
	public function hasParam() :bool
	{ return !empty($this->_parameters); }

	/**
	 * @param string $name
	 * @param string|int|float $default=null
	 * @return mixed
	 */
	public function getParam(string $name, $default = null)
	{ return array_key_exists($name, $this->_parameters) ?$this->_parameters[$name] :$default; }

	/** @return array */
	public function getParams() :array
	{ return (array) $this->_parameters; }

	/**
	 * @param bool $allParams=false
	 * @return array
	 */
	protected function _getAllParams($allParams = false) :array
	{
		if(!$allParams && $this->isQuotedParams()) return [];
		if(!$this->_hasParamsCache()){
			$this->_cacheParameters = (array) $this->_onGetChildesParams();
			$this->_cacheParameters = array_merge($this->_cacheParameters, $this->_parameters);
			$this->_cacheParametersCreated = microtime(true);
		} return $this->_cacheParameters;
	}

	/** @return array */
	abstract protected function _onGetChildesParams() :array;

	/**
	 * @param string|int|float|null $value
	 * @param string $name=null
	 * @return $this
	 */
	public function setParam($value, $name = null)
	{
		$this->_clearCache();
		if(null===$name) $name = uniqid(':P');
		if(is_string($value) || is_numeric($value)) {
			$this->_parameters[$name] = $value;
		}elseif (null===$value)
			$this->_parameters[$name] = null;
		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function deleteParam(string $name)
	{ $this->_clearCache(); unset($this->_parameters[$name]); return $this; }

	/** @return $this */
	public function clearParams()
	{ $this->_clearCache(); $this->_parameters = []; return $this; }










	/**
	 * @param int|float|string|array $value
	 * @param string|null $name=null
	 * @return string
	 */
	public function P($value, $name = null)
	{
		$this->_clearCache();
		if(is_null($value)) {
			return 'NULL';
		}elseif (is_string($value) && $value=='') {
			return '';
		}elseif (is_numeric($value) && in_array($value, [0, '0', 0.0])){
			return 0;
		}elseif (is_string($value) || is_numeric($value)){
			if(null===$name) $name = uniqid(':P');
			$this->setParam($value, $name);
			return $name;
		}elseif(is_array($value)){
			$name = null===$name ?uniqid(':P') :rtrim($name, '_').'_';
			$i = 1;
			$names = [];
			foreach ($value as $v){
				while (array_key_exists("{$name}{$i}", $this->_parameters)) $i++;
				$n = "{$name}{$i}";
				if($n=$this->P($v, $n)){
					$names[] = $n;
					$i++;
				}
			}
			return '('.implode(',', $names).')';
		}
		return '';
	}

	/**
	 * alias quote method on connection
	 * @param string|array $value
	 * @param int $parameter_type=Database::PARAM_STR
	 * @return string|array
	 */
	public function QV($value, int $parameter_type = Connection::PARAM_STR)
	{
		return $this->getConnection()
			?$this->getConnection()->quote($value, $parameter_type)
			:('$V['.(is_array($value) ?implode('|', $value) :(string) $value).']');
	}

	/**
	 * alias quoteTable method on connection
	 * @param string $name
	 * @param string $alias=null
	 * @return string
	 */
	public function QT(string $name, string $alias = null)
	{
		return $this->getConnection()
			?$this->getConnection()->quoteTable($name, $alias)
			:'$T['.$name.']'.($alias ?"AS[{$alias}]" :'');
	}

	/**
	 * alias quoteName method on connection
	 * @param string $name
	 * @param string $alias=null
	 * @return string
	 */
	public function QN(string $name, string $alias = null)
	{
		return $this->getConnection()
			?$this->getConnection()->quoteName($name, $alias)
			:'$N['.$name.']'.($alias ?"AS[{$alias}]" :'');
	}









	/** @return bool */
	protected function _hasQueryStringCache() :bool
	{ return (bool) $this->_cacheQueryStringCreated; }

	/** @return bool */
	protected function _hasParamsCache() :bool
	{ return (bool) $this->_cacheParametersCreated; }

	protected function _clearCache()
	{
		$this->_cacheQueryStringCreated = 0;
		$this->_cacheQueryString = '';
		$this->_cacheParametersCreated = 0;
		$this->_cacheParameters = [];
		if($this->hasParent()) $this->getParent()->_clearCache();
	}





	abstract protected function _onToSqlString() :string ;

	/** @return string */
	final public function toString() :string{
		if(!$this->_hasQueryStringCache()){
			$this->_cacheQueryString = $this->_onToSqlString();
			$params=$this->_getAllParams(true);
			if($this->isQuotedParams() && $params){
				foreach ($params as $name=>$value)
					$this->_cacheQueryString = str_replace($name, $this->QV($value), $this->_cacheQueryString);
			}
			$this->_cacheQueryStringCreated = microtime(true);
		} return $this->_cacheQueryString;
	}

	final public function __toString() :string
	{ return $this->toString(); }
}