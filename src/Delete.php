<?php
namespace Mtchabok\Database;
use Mtchabok\Database\Base\Query;
use Mtchabok\Database\Methods\FromMethods;
use Mtchabok\Database\Methods\JoinMethods;
use Mtchabok\Database\Methods\WhereMethods;
/**
 * Class Delete
 * @package Mtchabok\Database
 */
class Delete extends Query
{
    use FromMethods;
    use JoinMethods;
	use WhereMethods;

	/**
	 * @return Delete
	 */
	public function newThisDelete()
	{
		$delete = new static();
		if($this->hasConnection()) $delete->setConnection($this->getConnection());
		if($this->hasFrom()) $delete->setFrom($this->getFrom());
		return $delete;
	}

	/**
	 * @param string $tableName = null
	 * @return Delete
	 */
	public static function newDelete(string $tableName = null)
	{ return new static($tableName); }

	/**
	 * Insert constructor.
	 * @param string $tableName = null
	 */
	public function __construct(string $tableName = null)
	{
		if (null!==$tableName) $this->setFrom($this->QT($tableName));
	}



	protected function _onGetChildesParams(): array
	{
		$params = array_merge([], $this->_fromParams(), $this->_joinParams(),$this->_whereParams());
		return $params;
	}


	protected function _onToSqlString(): string
	{
		$sql = "DELETE {$this->_fromToString()}";
		if($this->hasJoin() && ($joinSql = $this->_joinToString()))
		    $sql.= " {$joinSql}";
		if($this->hasWhere() && ($where = $this->_whereToString())){
			$sql.= " {$where}";
		}
		return $sql;
	}

}