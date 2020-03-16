<?php
namespace Mtchabok\Database;

use Exception;

/**
 * Class Connections
 * @package Mtchabok\Database
 */
class Connections
{
    private $_defaultConnectionName = '';
    private $_connections = [];
    private $_names = [];


    /**
     * @param Connection $connection
     * @param string $name
     * @return Connection
     */
    protected function _onAddConnection($connection, string $name)
    { return $connection; }

    /**
     * @param string $name=null
     * @return Connection
     */
    protected function _onGetConnection(string $name = null)
    { return null; }

    protected function _onInit()
    {}







    /** @return string */
    final public function getDefaultConnectionName() :string
    { return $this->_defaultConnectionName; }

    /**
     * @param string $name
     * @throws Exception
     */
    final public function setDefaultConnectionName(string $name)
    {
        if(empty($name)) throw new Exception('default name is empty');
        if(!empty($this->_defaultConnectionName))
            throw new Exception('once set default name');
        $this->_defaultConnectionName = (string) $name;
    }








    /**
     * @param string $name=null
     * @param Connection $connection
     * @return Connection
     * @throws Exception
     */
    final public function addConnection($connection, string $name = null) :Connection
    {
        if(!$connection instanceof Connection || empty($connection->id))
            throw new Exception('variable is not Connection');
        if(empty($name = is_null($name) ?$this->getDefaultConnectionName() :(string) $name))
            throw new Exception('connection name is empty');
        if(array_key_exists($connection->id, $this->_connections) || array_key_exists($name, $this->_names))
            throw new Exception("Connection '{$connection->id}-{$name}' exists");
        if(($connection = $this->_onAddConnection($connection, $name)) instanceof Connection) {
            $this->_connections[$connection->id] = $connection;
            $this->_names[$name] = $connection->id;
        } return $connection;
    }

    /**
     * @param Connection|string|null $name
     * @return bool
     */
    final public function existConnection($name=null) :bool
    {
        return $name instanceof Connection ?array_key_exists($name->id, $this->_connections)
            :array_key_exists(is_null($name) ?$this->getDefaultConnectionName() :(string) $name, $this->_names);
    }


    /**
     * @param string $name=null
     * @return Connection
     * @throws Exception
     */
    final public function getConnection(string $name = null) :Connection
    {
        if(is_null($name)) $name = $this->getDefaultConnectionName();
        if(empty($name)) throw new Exception('connection name empty');
        if(!array_key_exists($name, $this->_names) || !array_key_exists($this->_names[$name], $this->_connections))
            $connection = $this->_onGetConnection($name);
        else
            $connection = $this->_connections[$this->_names[$name]];
        if(!$connection instanceof Connection)
            throw new Exception('not exist connection');
        if(!$this->existConnection($connection))
            $this->addConnection($connection, $name);
        return $connection;
    }

    /**
     * @param Connection $connection
     * @return string
     * @throws Exception
     */
    final public function getConnectionName(Connection $connection) :string
    {
        if(!$this->existConnection($connection) || !($name = array_search($connection->id, $this->_names)))
            throw new Exception("connection '{$connection->id}' not exists");
        return $name;
    }


    /**
     * @param Connection $connection
     * @param string $newName
     * @return bool
     * @throws Exception
     */
    final public function changeConnectionName(Connection $connection, string $newName) :bool
    {
        if(empty($newName)) throw new Exception("connection new name empty");
        $oldName = $this->getConnectionName($connection);
        if(array_key_exists($newName, $this->_names))
            throw new Exception("connection new name '{$newName}' exists");
        unset($this->_names[$oldName]);
        $this->_names[$newName] = $connection->id;
        return true;
    }









    /**
     * @return Connections
     */
    final public static function newConnections()
    { return new static(); }


    final public function __construct()
    { $this->_onInit(); }


}
