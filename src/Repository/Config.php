<?php

namespace BranchLabs\EnvSwitcher\Repository;

use Illuminate\Config\Repository;
use Symfony\Component\Finder\Finder;

class Config extends Repository {

    /**
     * The directory containing the config files
     * @var string
     */
    protected $configPath;

    /**
     * Load the configuration items from all of the files.
     * @param $path
     */
    public function loadConfigurationFiles($path) {
        $this->configPath = $path;
        $configFiles = $this->getConfigurationFiles();

        foreach($configFiles as $fileKey => $path) {
            $this->set($fileKey, require $path);
        }
    }

    /**
     * Get the configuration files for the selected environment
     * @return array
     */
    protected function getConfigurationFiles() {
        $path = $this->configPath;
        if (!is_dir($path)) {
            return [];
        }
        $files = [];
        foreach (Finder::create()->files()->name('*.php')->in($path)->depth(0) as $file) {
            $files[basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }
        return $files;
    }
}