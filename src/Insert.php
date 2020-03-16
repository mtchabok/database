<?php
namespace Mtchabok\Database;
use Mtchabok\Database\Methods\FieldMethods;
use Mtchabok\Database\Base\Query;
/**
 * Class Insert
 * @package Mtchabok\Database
 */
class Insert extends Query
{
    use FieldMethods;


	protected $_into    = '';
	protected $_rows    = [];
	protected $_selectMode = false;
	protected $_updateOnDuplicateKey = '';



	/** @return bool */
	public function hasInto() :bool
	{ return !empty($this->_into); }

	/** @return string */
	public function getInto() :string
	{ return (string) $this->_into; }

	/**
	 * @param string $tableName
	 * @return $this
	 */
	public function setInto(string $tableName)
	{
		if($this->_into!=(string) $tableName){
			$this->_clearCache();
			$this->_into = (string) $tableName;
		} return $this;
	}









    /** @return bool */
    public function hasRows() :bool
    { return !empty($this->_rows); }

	/** @return array */
	public function getRows()
	{ return $this->_rows; }

	/** @return int */
	public function getNumRows() :int
	{ return count($this->_rows); }

	/** @return bool */
	public function isSelectMode() :bool
	{ return $this->_selectMode; }

	/**
	 * @param array|object|string|Select $rows
	 * @return Insert|false
	 */
	public function setRows($rows)
	{
		$this->_rows = [];
		$this->clearParams();
		$this->_clearCache();
		$this->_selectMode = false;
		if($rows && (is_string($rows) || $rows instanceof Select)){
			if($this->addRow($rows))
				return false;
		}elseif ($rows && (is_array($rows) || is_object($rows))){
			if(is_object($rows)) $rows = [$rows];
			foreach ($rows as $row){
				if($row && !$this->addRow($row))
					return false;
			}
		}else return false;
		return $this;
	}

	/**
	 * @param array|object|string|Select $row
	 * @return Insert|false
	 */
	public function addRow($row)
	{
		$this->_clearCache();
		if((is_string($row) || $row instanceof Select) && $row){
			$this->_selectMode = true;
			$this->_rows[] = $row;
			if($row instanceof Select) $row->setParent($this);
		}elseif ($row && (is_array($row) || is_object($row))){
			if(is_object($row)) $row = get_object_vars($row);
			$fields = array_keys($row);
			// add new fields
			$rowInsert = [];
			if(!is_int($fields[0])) {
				foreach ($fields as $field) {
					if (!is_int($field) && ($this->existField($field) || $this->addField($field))) {
						$rowInsert[$field] = $row[$field];
					}
				}
			}
			$this->_rows[] = $rowInsert ?$rowInsert :array_values($row);
		}else return false;
		return $this;
	}

















	/** @return bool */
	public function isUpdateOnDuplicateKey() :bool
	{ return $this->_updateOnDuplicateKey; }

	/** @return string */
	public function getUpdateOnDuplicateKey()
	{ return $this->_updateOnDuplicateKey; }

	/**
	 * @param true|string $updateRow
	 * @return $this
	 */
	public function setUpdateOnDuplicateKey($updateRow)
	{
		$this->_clearCache();
		$this->_updateOnDuplicateKey = true===$updateRow ?true :(string) $updateRow;
		return $this;
	}
	








	/**
	 * @param string|array|object|Select $rows=null
	 * @param array $fields=null
	 * @return Insert
	 */
	public function newThisInsert($rows = null, array $fields = null)
	{
		$insert = new static();
		if($this->hasConnection()) $insert->setConnection($this->getConnection());
		if($this->hasInto()) $insert->setInto($this->getInto());
		if(null!==$fields) $insert->setField($fields);
		if(null!==$rows) $insert->setRows($rows);
		return $insert;
	}

	/**
	 * @param string $into = null
	 * @param string|array|object|Select $rows=null
	 * @param array $fields=null
	 * @return Insert
	 */
	public static function newInsert(string $into = null, $rows = null, array $fields = null)
	{ return new static($into, $rows, $fields); }


	/**
	 * Insert constructor.
	 * @param string $into = null
	 * @param string|array|object|Select $rows=null
	 * @param array $fields=null
	 */
	public function __construct(string $into = null, $rows = null, array $fields = null)
	{
		$this->_allowFieldAlias = false;
		if (null!==$into) $this->setInto($into);
		if (null!==$fields) $this->setField($fields);
		if (null!==$rows) $this->setRows($rows);
	}

	protected function _onGetChildesParams(): array
	{
		$params = array_merge([], $this->_fieldParams());
		if($this->isSelectMode()){
			foreach ($this->_rows as $row){
				if($row instanceof Select && !$row->isQuotedParams())
					$params = array_merge($params, $row->_getAllParams());
			}
		}
		return $params;
	}

	protected function _onToSqlString(): string
	{
		$sql = 'INSERT'.' INTO ' . $this->QT($this->getInto());
        if ($this->hasField() && ($fieldsSql=$this->_fieldToString()))
            $sql.= " ({$fieldsSql})";
        $fieldsSql=null;
		$addRowsOrSelect = false;

		if($this->hasRows()){
			$qRows = [];
			foreach ($this->getRows() as $row) {
				$qRow = null;
				if($row && (is_string($row) || $row instanceof Select)) {
					$qRow = (string)$row;
				}elseif ($row && is_array($row)){
					$qRow = [];
					$fields = array_keys($row);
					$hasField = !is_int($fields[0]);
					if ($this->hasField() && $hasField) {
						foreach ($this->getField() as $field) $qRow[] = null===$row[$field] ?'NULL' :$this->P($row[$field]);
					}elseif ($this->hasField() && !$hasField){
						for($i=0; $i<$this->getNumFields(); $i++){
							if(isset($row[$i])){
								$qRow[] = null===$row[$i] ?'NULL' :$this->P($row[$i]);
							}else break;
						}
					} else {
						foreach ($row as $value)
							$qRow[] = null === $value ? 'NULL' : $this->P($value);
					}
				}
				if($qRow && $this->isSelectMode()){
					if (is_string($qRow))
						$qRows[] = $qRow;
					elseif (is_array($qRow)){
						$qRows[] = 'SELECT ' . implode(',', $qRow);
					}
				}elseif ($qRow && is_array($qRow)){
					$qRows[] = '(' . implode(',', $qRow) . ')';
				}
			}
			if ($qRows) {
				if($this->isSelectMode())
					$sql .= ' ' . implode(' UNION ALL ', $qRows);
				else
					$sql .= ' VALUES ' . implode(',', $qRows);
				$addRowsOrSelect = true;
			}
			unset($qRows);
		}
		if ($this->isUpdateOnDuplicateKey() && $addRowsOrSelect){
			$update = $this->getUpdateOnDuplicateKey();
			if(true===$update){
				if($this->hasField()){
					$qFields = [];
					foreach ($this->getField() as $field)
						$qFields[] = $this->QN($field) . '=VALUES(' . $this->QN($field) . ')';
					$sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $qFields);
				}
			}elseif ($update)
				$sql .= " ON DUPLICATE KEY UPDATE {$update}";
		}
		return $sql;
	}
}