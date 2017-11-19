<?php

namespace BranchLabs\EnvSwitcher\Helpers;

use \Mage;

class MagentoHelper {

    /**
     * Get the proper table name for a given table (typically adds prefix, if necessary).
     * @param $tableName
     * @return string
     */
    public static function getTableName($tableName) {
        $resource = Mage::getSingleton('core/resource');
        return $resource->getTableName($tableName);
    }

    /**
     * Run a write query against the database
     * @param $statement The SQL statement
     * @param array $params Params to pass to PDO
     */
    public static function customWriteQuery($statement, $params = []) {

        $resource = Mage::getSingleton('core/resource');
        $dbWrite = $resource->getConnection('core_write');
        $dbWrite->query($statement, $params);

        echo "# RUNNING QUERY:" . PHP_EOL .
            $statement . PHP_EOL;
        if( count($params) > 0 ) {
            echo "# PARAMS: " . PHP_EOL .
            var_export($params, true) . PHP_EOL;
        }
        echo PHP_EOL;
    }

    /**
     * Set all database cache values to disabled
     */
    public static function disableCaches() {
        $table = MagentoHelper::getTableName('core_cache_option');
        $query = "UPDATE {$table} SET value = '0';";
        MagentoHelper::customWriteQuery($query);
    }

    /**
     * Remove email content and recipient from the email queues
     */
    public static function clearEmailQueue() {
        MagentoHelper::customWriteQuery(
            'DELETE FROM ' . MagentoHelper::getTableName('core_email_queue_recipients') . ';'
        );

        MagentoHelper::customWriteQuery(
            'DELETE FROM ' . MagentoHelper::getTableName('core_email_queue') . ';'
        );
    }
}