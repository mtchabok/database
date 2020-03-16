<?php
namespace Mtchabok\Database;
use Mtchabok\Database\Base\Query;
use Mtchabok\Database\Methods\FieldMethods
    , Mtchabok\Database\Methods\FromMethods
    , Mtchabok\Database\Methods\JoinMethods
    , Mtchabok\Database\Methods\WhereMethods
	, Mtchabok\Database\Methods\FetchMethods;
use Mtchabok\Database\Methods\OrderingMethods;

/**
 * Class Select
 * @package Mtchabok\Database
 */
class Select extends Query
{
	use FieldMethods;
	use FromMethods;
	use JoinMethods;
	use WhereMethods;
	use OrderingMethods;
	use FetchMethods;












	/** @return Select */
	public function newThisSelect()
	{
		$select = new static();
		if($this->hasConnection()) $select->setConnection($this->getConnection());
		if($this->hasFrom()) $select->setFrom($this->getFrom());
		if($this->hasField()) $select->setField($this->getField());
		return $select;
	}

	/**
	 * @param string|array|Select $from
	 * @param array|string $fields=null
	 * @return Select
	 */
	public static function newSelect($from = null, $fields = null)
	{ return new static($from, $fields); }


	/**
	 * Select constructor.
	 * @param string|array|Select $from=null
	 * @param array|string $fields=null
	 */
	public function __construct($from = null, $fields = null)
	{
		if(!is_null($from)) $this->setFrom($this->QT($from));
		if(!is_null($fields)) $this->setField($fields);
	}




	protected function _onGetChildesParams(): array
	{
		$params = array_merge([], $this->_fieldParams(), $this->_fromParams(), $this->_joinParams(), $this->_whereParams());
		return $params;
	}

	protected function _onToSqlString(): string
	{
		$sql = 'SELECT';
		if($this->hasField() && ($field=$this->_fieldToString()))
			$sql.= " {$field}";
		else $sql.= ' *';
		unset($field);
		if($this->hasFrom() && ($from=$this->_fromToString()))
			$sql.= " {$from}";
		unset($from);
		if($joins = $this->_joinToString())
			$sql.= " {$joins}";
		unset($joins);
		if($this->hasWhere() && ($where = $this->_whereToString()))
			$sql.= " {$where}";
		unset($where);
		if($this->hasOrderBy() && ($orderBy = $this->_orderingToString()))
			$sql.= " {$orderBy}";
		unset($orderBy);
		return $sql;
	}


}