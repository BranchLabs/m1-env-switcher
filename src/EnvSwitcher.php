<?php

namespace BranchLabs\EnvSwitcher;

use Dotenv\Dotenv;
use BranchLabs\EnvSwitcher\Repository\Config;
use BranchLabs\EnvSwitcher\Helpers\MagentoHelper;

class EnvSwitcher {


    const CONFIG_DIR = 'shell/EnvSwitcher/config/';

    protected $shellArgs = [];
    protected $environment;

    protected $configPath;
    protected $allowedEnvironments = [];
    protected $config;

    /**
     * EnvSwitcher constructor.
     * @param $shellArgs The shell argument array as provided by Mage_Shell_Abstract::_args
     * @param $allowedEnvironments an array of valid environments to run the migration as.
     *  Likely values: dev, staging, integration
     */
    public function __construct($shellArgs, $allowedEnvironments) {
        $this->shellArgs = $shellArgs;
        $this->allowedEnvironments = $allowedEnvironments;

        $this->_loadDotEnv();
        $this->_parseArgs();
        $this->_loadConfigs();
    }

    /**
     * This method executes the major migration pieces:
     *  - reading/updating core_config_data settings
     *  - disabling caches where necessary
     *  - clears the email queue
     * and any other future functionality we want to add to the library.
     */
    public function run() {
        $this->_updateDefaultConfigs();
        $this->toggleCache();
        $this->clearMailQueue();
    }

    private function _loadDotEnv() {
        $dotEnv = new Dotenv(getcwd());
        if( file_exists('.env') ) {
            $dotEnv->load();
        }

    }

    /**
     * Verify that the environment variable is present and a valid code.
     * If we support more flags/args in the future (dry run, verbose, etc.)
     * here's where we could parse and set the proper member variables.
     */
    private function _parseArgs() {
        $this->environment = $this->shellArgs['env'];

        if( empty($this->environment) ) {
            echo "Environment must be set via `--env XYZ`" . PHP_EOL . 'Exiting...' . PHP_EOL;
            exit;
        }

        if( !in_array($this->environment, $this->allowedEnvironments) ) {
            echo "Unknown environment: '" . $this->environment . "'" .  PHP_EOL . 'Exiting...' . PHP_EOL;
            exit;
        }
    }

    /**
     * Load config files/values
     */
    private function _loadConfigs() {

        $this->configPath = getcwd() . '/' . self::CONFIG_DIR;

        $this->config = new Config();
        $this->config->loadConfigurationFiles($this->configPath);
    }

    /**
     * Process the `core_config_data` table updates.  This is where the majority of
     * the migration action happens.
     */
    private function _updateDefaultConfigs() {
        // read the common core_config_data updates, then merge them with
        // environment-specific values
        $configUpdates = $this->config->get('core-config-data.all');
        $envUpdates = $this->config->get('core-config-data.' . $this->environment);

        if( $envUpdates ) {
            $configUpdates = array_merge($configUpdates, $envUpdates);
        }

        // process all of the configuration values
        $this->bulkUpdateConfig($configUpdates);
    }

    /**
     * Save a CoreConfig object
     * @param CoreConfig $config
     */
    protected function updateConfig(CoreConfig $config) {
        $config->save();
    }

    /**
     * Save an array of CoreConfig objects
     * @param array $configs
     */
    protected function updateConfigs($configs = []) {
        foreach($configs as $config) {
            $this->updateConfig($config);
        }
    }

    /**
     * Handle the updates from the core config data configuration settings.
     * @param array $configArray
     */
    protected function bulkUpdateConfig($configArray = []) {
        foreach($configArray as $i => $configuration) {
            if(gettype($configuration) == 'object' && get_class($configuration) == CoreConfig::class ) {
                // no need to do anything with the object
            } else {
                // build configuration object(s) from the config value(s)
                $configuration = CoreConfig::build($i, $configuration);
            }

            // depending on the configuration we might have a single config object, or multiple
            if( is_array($configuration) ) {
                $this->updateConfigs($configuration);
            } else {
                $this->updateConfig($configuration);
            }

        }
    }

    /**
     * toggle cache based on config settings
     */
    protected function toggleCache() {
        $configUpdates = $this->config->get('cache.all');
        $envUpdates = $this->config->get('cache.' . $this->environment);

        if( $envUpdates ) {
            $configUpdates = array_merge($configUpdates, $envUpdates);
        }

        if(isset($configUpdates['disable_cache']) && $configUpdates['disable_cache'] === true) {
            MagentoHelper::disableCaches();
        }
    }

    /**
     * clear mail queue based on config settings
     */
    protected function clearMailQueue() {
        $configUpdates = $this->config->get('mail.all');
        $envUpdates = $this->config->get('mail.' . $this->environment);

        if( $envUpdates ) {
            $configUpdates = array_merge($configUpdates, $envUpdates);
        }

        if(isset($configUpdates['empty_queue']) && $configUpdates['empty_queue'] === true) {
            MagentoHelper::clearEmailQueue();
        }
    }

    /**
     * Display the help message
     * @return string
     */
    protected function usageHelp() {
        return <<<USAGE
Usage:  php -f shell/EnvSwitcher/Migrate.php -- [options]

  --env <environment>           The environment to which we are migrating (typically dev or staging).
                                This determines which values we will use to update the database.
  help                          This help dialog.

USAGE;
    }
}
