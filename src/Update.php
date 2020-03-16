<?php
/**
 * Created by PhpStorm.
 * Project: mtchabok_database
 * User: mtchabok
 * Date: 2019-05-19
 * Time: 11:16 PM
 */

namespace Mtchabok\Database;
use Mtchabok\Database\Base\Query;
use Mtchabok\Database\Methods\WhereMethods;

/**
 * Class Update
 * @package Mtchabok\Database
 */
class Update extends Query
{
	use WhereMethods;

	/** @var string */
	protected $_table = '';
	/** @var array */
	protected $_fields = [];
	/** @var array */
	protected $_rows = [];
	/** @var array */
	protected $_keys = [];




	/** @return bool */
	public function hasTable() :bool
	{ return !empty($this->_table); }

	/** @return string */
	public function getTable() :string
	{ return (string) $this->_table; }

	/**
	 * @param string $tableName
	 * @return $this
	 */
	public function setTable(string $tableName)
	{
		if($this->_table!=(string) $tableName) {
			$this->_clearCache();
			$this->_table = (string)$tableName;
		} return $this;
	}







	/** @return bool */
	public function hasRows() :bool
	{ return !empty($this->_rows); }

	/** @return array */
	public function getRows() :array
	{ return $this->_rows; }

	/** @return int */
	public function getNumRows() :int
	{ return count($this->_rows); }

	/**
	 * @param array|object $rows
	 * @param bool $ignoreErrorOnAddRows
	 * @return Update|false
	 */
	public function setRows(array $rows, $ignoreErrorOnAddRows = false)
	{
		$this->_rows = [];
		$this->_clearCache();
		$this->clearParams();
		if(is_object($rows)) $rows = [$rows];
		elseif (!is_array($rows)) return false;
		elseif (!is_array(current($rows)) && !is_object(current($rows)))
			$rows = [$rows];
		foreach ($rows as $row){
			if($row && !$this->addRow($row) && !$ignoreErrorOnAddRows)
				return false;
		} return $this;
	}

	/**
	 * @param object|array $row
	 * @return Update|false
	 */
	public function addRow($row)
	{
		$this->_clearCache();
		if (is_object($row)) $row = get_object_vars($row);
		elseif (!is_array($row) || !$row) return false;
		$fields = array_keys($row);
		$hasFields = !is_int($fields[0]);
		if(!$this->hasFields() && $hasFields) {
			$this->setFields($fields);
			if(!$this->hasFields()) $hasFields = false;
		}
		unset($fields);
		$rowUpdate = [];
		if($this->hasFields() && $hasFields){
			foreach ($this->getFields() as $field)
				$rowUpdate[$field] = isset($row[$field]) ?$row[$field] :null;
		}elseif ($this->hasFields()){
			foreach ($this->getFields() as $field){
				$rowUpdate[$field] = array_shift($row);
			}
		}else return false;
		if($rowUpdate) $this->_rows[] = $rowUpdate;
		return $this;
	}









	/** @return bool */
	public function hasKeys() :bool
	{ return !empty($this->_keys); }

	/** @return array */
	public function getKeys() :array
	{ return (array) $this->_keys; }

	/** @return int */
	public function getNumKeys() :int
	{ return count($this->_keys); }

	/**
	 * @param string|array $tableKeyFields
	 * @return Update
	 */
	public function setKeys($tableKeyFields) :Update
	{
		$this->_clearCache();
		if(!is_array($tableKeyFields)) $tableKeyFields = [(string)$tableKeyFields];
		$this->_keys = [];
		foreach ($tableKeyFields as $tableKeyField)
			if($tableKeyField) $this->_keys[] = $tableKeyField;
		return $this;
	}






	/** @return bool */
	public function hasFields() :bool
	{ return !empty($this->_fields); }

	/** @return array */
	public function getFields() :array
	{ return $this->_fields; }

	/**
	 * @param array $fields
	 * @return Update
	 */
	public function setFields(array $fields) :Update
	{
		$this->_fields = [];
		$this->_clearCache();
		$this->setRows([]);
		foreach ($fields as $field) if($field && is_string($field)) $this->_fields[] = (string) $field;
		return $this;
	}




















	/**
	 * @param array|object $rows=null
	 * @param string|array $tableKeys=null
	 * @return Update
	 */
	public function newThisUpdate($rows = null, $tableKeys = null)
	{
		$update = new static();
		if($this->hasConnection()) $update->setConnection($this->getConnection());
		if($this->hasTable()) $update->setTable($this->getTable());
		if(null!==$tableKeys) $this->setKeys($tableKeys);
		if(null!==$rows) $update->setRows($rows);
		return $update;
	}

	/**
	 * @param string $table = null
	 * @param array|object $rows=null
	 * @param string|array $tableKeys=null
	 * @return Update
	 */
	public static function newUpdate(string $table = null, $rows = null, $tableKeys = null)
	{ return new static($table, $rows, $tableKeys); }

	/**
	 * Insert constructor.
	 * @param string $table = null
	 * @param array|object $rows=null
	 * @param string|array $tableKeys=null
	 */
	public function __construct(string $table = null, $rows = null, $tableKeys = null)
	{
		if (null!==$table) $this->setTable($table);
		if (null!==$tableKeys) $this->setKeys($tableKeys);
		if (null!==$rows) $this->setRows($rows);
	}




	protected function _onGetChildesParams(): array
	{
		$params = array_merge([], $this->_whereParams());
		return $params;
	}

	protected function _onToSqlString(): string
	{
		$sql = 'UPDATE '.$this->QT($this->getTable());
		$rows = $this->getRows();
		$keys = $this->getKeys();
		if($this->hasRows()){
			$sql.= ' SET ';
			if(count($rows)===1){
				$qRow = [];
				$row = current($rows);
				foreach ($this->getFields() as $field){
					if(!in_array($field, $keys))
						$qRow[] = $this->QN($field).'='.$this->P($row[$field]);
				}
				if($qRow) $sql.= implode(',', $qRow);
			}elseif($keys){
				$qFields = [];
				foreach ($rows as $row){
					$qKeys = [];
					foreach ($keys as $key) {
						$value = isset($row[$key]) ?$row[$key] :null;
						$qKeys[] = $this->QN($key) . (null === $value ? 'IS NULL' : '=' . $this->P($value));
					}
					$qKeys = implode(' AND ', $qKeys);
					foreach (array_merge($keys, $this->getFields()) as $field){
						if(in_array($field, $keys)) continue;
						if(!isset($qFields[$field])) $qFields[$field] = '';
						$value = isset($row[$field]) ?$row[$field] :null;
						$qFields[$field].= ' WHEN '.$qKeys.' THEN '.$this->P($value);
					}
				}
				foreach ($qFields as $name=>&$qField){
					$qField = $this->QN($name).'=(CASE'.$qField.' ELSE '.$this->QN($name).' END)';
				}
				$sql.= ' '.implode(',', $qFields);
			}
		}
		if($this->hasWhere()) {
			if($where = $this->_whereToString())
				$sql .= " {$where}";
			unset($where);
		}elseif($this->hasKeys()){
			$qKeys = [];
			foreach ($rows as $row){
				foreach ($keys as $key) {
					if(!isset($qKeys[$key])) $qKeys[$key]=[];
					$qKeys[$key][] = $this->P($row[$key]);
				}
			}
			if($qKeys){
				foreach ($qKeys as $name=>$qKey){
					$qKeys[$name] = $this->QN($name).' IN ('.implode(',', $qKey).')';
				}
				$sql.= ' WHERE '.implode(' AND ', $qKeys);
			}
		}
		return $sql;
	}


}