<?php
require_once( dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'abstract.php');

use BranchLabs\EnvSwitcher\EnvSwitcher;
use BranchLabs\EnvSwitcher\Helpers\MagentoHelper;

class Mage_Shell_ExampleUsage_Migrate extends Mage_Shell_Abstract {

    /**
     * define our environment codes as consts so we can
     * reference them in multiple areas cleanly.
     */
    const DEV_ENVIRONMENT = 'dev';
    const STAGING_ENVIRONMENT = 'staging';

    protected $envSwitcher;

    /**
     * Like all properly-written magento shell scripts, this is the entry point of the command
     */
    public function run() {
        $allowedEnvironments = $this->getAllowedEnvironments();
        $configPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

        $this->envSwitcher = new EnvSwitcher($this->_args, $allowedEnvironments, $configPath);
        $this->envSwitcher->run();

        // additional scripts as required

//        MagentoHelper::customWriteQuery(
//            'UPDATE ' . MagentoHelper::getTableName('some_table') . ' SET some_column = :value;',[
//                'value' => 'abc'
//            ]
//        );
    }

    /**
     * @return array
     * We use this method to pass a list of allowed environment codes
     * to the EnvSwitcher library so it knows to warn us when an invalid
     * env is passed
     */
    protected function getAllowedEnvironments() {
        return [
            self::DEV_ENVIRONMENT,
            self::STAGING_ENVIRONMENT
        ];
    }
}

$shell = new Mage_Shell_ExampleUsage_Migrate();
$shell->run();