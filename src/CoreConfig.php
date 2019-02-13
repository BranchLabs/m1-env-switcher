<?php

namespace BranchLabs\EnvSwitcher;

use \Mage;
use BranchLabs\EnvSwitcher\Helpers\MagentoHelper;

class CoreConfig {

    private $_tableName;
    protected $path;
    protected $value;
    protected $scope;
    protected $scopeId;

    /**
     * CoreConfig constructor.
     * @param $path
     * @param $value
     * @param string $scope
     * @param int $scopeId
     */
    public function __construct($path, $value, $scope = 'default', $scopeId = 0) {

        $this->_tableName = MagentoHelper::getTableName('core_config_data');
        $this->path = $path;
        $this->value = $value;
        $this->scope = $scope;
        $this->scopeId = $scopeId;
    }

    /**
     * @param $path
     * @param $data
     * @return array|CoreConfig
     */
    public static function build($path, $data) {

        if( gettype($data) == 'string' ) {
            return new CoreConfig($path, $data, null, null);

        } elseif( gettype($data) == 'array' ) {
            $configArray = [];
            foreach($data as $scopeCode => $value) {

                $scopeId = self::getScopeIdFromCode($scopeCode);
                $scope = self::getScopeFromCode($scopeCode);

                $configArray[] = new CoreConfig($path, $value, $scope, $scopeId);
            }
            return $configArray;

        }
    }

    /**
     * Persist the updated config values to the database
     */
    public function save() {
        if( $this->coreConfigRowExists() ) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    /**
     * update the core_config_data row
     */
    private function update() {
        if( empty($this->scope) && empty($this->scopeId) ) {
            // if a scope or scopeId are not provided,
            // we want to update all records of the config path with a new value
            $statement = "UPDATE {$this->_tableName} SET value = :value WHERE path = :path;";
            MagentoHelper::customWriteQuery($statement, [
                'value' => $this->value,
                'path' => $this->path
            ]);

        } else {
            //if a scope and scopeId are provided, only update the value where the path, scope, and scopeId match

            $statement = "UPDATE {$this->_tableName} SET value = :value WHERE path = :path AND scope = :scope AND scope_id = :scopeId;";

            MagentoHelper::customWriteQuery($statement, [
                'value' => $this->value,
                'path' => $this->path,
                'scope' => $this->scope,
                'scopeId' => $this->scopeId,
            ]);
        }
    }

    /**
     * add a new core_config_data row
     */
    private function insert() {

        if (empty($this->scope) || empty($this->scopeId)) {
            $statement = "INSERT INTO {$this->_tableName} (value, path) VALUES(:value, :path);";
            $params = [
                'value' => $this->value,
                'path' => $this->path,
            ];

        } else {
            $statement = "INSERT INTO {$this->_tableName} (value, path, scope, scope_id) VALUES(:value, :path, :scope, :scopeId);";
            $params = [
                'value' => $this->value,
                'path' => $this->path,
                'scope' => $this->scope,
                'scopeId' => $this->scopeId,
            ];
        }

        MagentoHelper::customWriteQuery($statement, $params);
    }

    /**
     * Return whether a core_config_data row exists for the specified path (and optionally scope, scope_id)
     * @param $path
     * @param string $scope
     * @param int $scopeId
     * @return bool
     */
    private function coreConfigRowExists() {
        $resource = Mage::getSingleton('core/resource');
        $dbRead = $resource->getConnection('core_read');


        if (empty($this->scope) || empty($this->scopeId)) {
            $statement = "SELECT * FROM " . MagentoHelper::getTableName('core_config_data') . " WHERE path = :path;";
            $params =[
                'path' => $this->path
            ];

        } else {
            $statement = "SELECT * FROM " . MagentoHelper::getTableName('core_config_data')
                . " WHERE path = :path AND scope = :scope AND scope_id = :scopeId;";
            $params = [
                'path' => $this->path,
                'scope' => $this->scope,
                'scopeId' => $this->scopeId,
            ];
        }

        $readResult = $dbRead->fetchAll($statement, $params);

        return count($readResult) > 0;

    }

    /**
     * @param $scopeCode
     * @return int
     */
    private static function getScopeIdFromCode($scopeCode) {
        $codeParts = explode(':', $scopeCode);
        if( count($codeParts) == 1) {
            if($codeParts[0] == 'default') {
                return 0;
            }

            $websiteId = Mage::getModel('core/website')->load($codeParts[0], 'code')->getId();
            return $websiteId;
        } elseif( count($codeParts) == 2) {

            $websiteId = Mage::getModel('core/website')->load($codeParts[0], 'code')->getId();
            $storeId = Mage::app()->getStore($codeParts[1])->getId();

            return $storeId;
        }
        return 0;
    }

    private static function getScopeFromCode($scopeCode) {
        $codeParts = explode(':', $scopeCode);
        if( count($codeParts) == 1 && $codeParts[0] == 'default') {
            return 'default';
        } elseif( count($codeParts) == 1 ) {
            return 'websites';
        } else {
            return 'stores';
        }
    }
}
