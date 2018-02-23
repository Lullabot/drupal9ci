<?php

// @codingStandardsIgnoreStart

/**
 * Base tasks for setting up a module to test within a full Drupal environment.
 *
 * This file expects to be called from the root of a Drupal site.
 *
 * @class RoboFile
 * @codeCoverageIgnore
 */
class RoboFile extends \Robo\Tasks
{
    /**
     * @var string $db_url
     *   The database URL. This can be overridden by specifying a $DB_URL
     *   environment variable.
     */
    protected $db_url = 'mysql://root@127.0.0.1/drupal8';

    /**
     * RoboFile constructor.
     */
    public function __construct()
    {
        // Pull a DB_URL from the environment, if it exists.
        if (filter_var(getenv('DB_URL'), FILTER_VALIDATE_URL)) {
          $this->db_url = getenv('DB_URL');
        }
        // Treat this command like bash -e and exit as soon as there's a failure.
        $this->stopOnFail();
    }

    /**
     * Adds coding standard dependencies.
     */
    public function addCodingStandardsDeps()
    {
        $config = json_decode(file_get_contents('composer.json'));
        $config->require->{"drupal/coder"} = "^8.2";
        file_put_contents('composer.json', json_encode($config));
    }

    /**
     * Adds Behat dependencies.
     */
    public function addBehatDeps()
    {
        $config = json_decode(file_get_contents('composer.json'));
        $config->require->{"behat/mink-selenium2-driver"} = "^1.3";
        $config->require->{"drupal/drupal-extension"} = "master-dev";
        // Pin version until https://github.com/Behat/MinkExtension/pull/311 gets fixed.
        $config->require->{"behat/mink-extension"} = "v2.2";
        $config->require->{"drush/drush"} = "~8.1";
        $config->require->{"guzzlehttp/guzzle"} = "^6.0@dev";
        file_put_contents('composer.json', json_encode($config));
    }

  /**
   * Installs composer dependencies.
   */
  public function installDependencies()
  {
    $this->taskComposerInstall()
      ->optimizeAutoloader()
      ->run();
  }

    /**
     * Updates composer dependencies.
     */
    public function updateDependencies()
    {
        // The git checkout includes a composer.lock, and running composer update
        // on it fails for the first time.
        $this->taskFilesystemStack()->remove('composer.lock')->run();
        $this->taskComposerUpdate()
          ->optimizeAutoloader()
          ->run();
    }

    /**
     * Install Drupal.
     *
     * @param string $admin_user
     *   (optional) The administrator's username.
     * @param string $admin_password
     *   (optional) The administrator's password.
     * @param string $site_name
     *   (optional) The Drupal site name.
     */
    public function setupDrupal(
      $admin_user = null,
      $admin_password = null,
      $site_name = null
    ) {
        $task = $this->drush()
          ->args('site-install')
          ->option('yes')
          ->option('db-url', $this->db_url, '=');

        if ($admin_user) {
            $task->option('account-name', $admin_user, '=');
        }

        if ($admin_password) {
            $task->option('account-pass', $admin_password, '=');
        }

        if ($site_name) {
            $task->option('site-name', $site_name, '=');
        }

        // Sending email will fail, so we need to allow this to always pass.
        $this->stopOnFail(false);
        $task->run();
        $this->stopOnFail();
    }

    /**
     * Return drush with default arguments.
     *
     * @return \Robo\Task\Base\Exec
     *   A drush exec command.
     */
    protected function drush()
    {
        // Drush needs an absolute path to the docroot.
        $docroot = $this->getDocroot() . '/web';
        return $this->taskExec('vendor/bin/drush')
          ->option('root', $docroot, '=');
    }

    /**
     * Get the absolute path to the docroot.
     *
     * @return string
     */
    protected function getDocroot()
    {
        $docroot = (getcwd());
        return $docroot;
    }

    /**
     * Run PHPUnit and simpletests for the module.
     *
     * @param string $module
     *   The module name.
     */
    public function test($module)
    {
        $this->phpUnit($module)
          ->run();
    }

    /**
     * Return a configured phpunit task.
     *
     * This will check for PHPUnit configuration first in the module directory.
     * If no configuration is found, it will fall back to Drupal's core
     * directory.
     *
     * @param string $module
     *   The module name.
     *
     * @return \Robo\Task\Testing\PHPUnit
     */
    private function phpUnit($module)
    {
        return $this->taskPhpUnit('vendor/bin/phpunit')
          ->option('verbose')
          ->option('debug')
          ->configFile('web/core')
          ->group($module);
    }

    /**
     * Gathers coding standard statistics.
     *
     * @param string $path
     *   Path were cs.json and cs-practice.json files have been stored
     *   by the container where phpcs was executed.
     *
     * @return string
     *   A short string with the total violations.
     */
    public function extractCodingStandardsStats($path)
    {
        $errors = 0;
        $warnings = 0;

        if (file_exists($path . '/cs.json')) {
            $stats = json_decode(file_get_contents($path . '/cs.json'));
            $errors += $stats->totals->errors;
            $warnings += $stats->totals->warnings;
        }

        return $errors . ' errors and ' . $warnings . ' warnings.';
    }

    /**
     * Gathers code coverage stats.
     *
     * @param string $path
     *   Path to a Clover report file.
     *
     * @return string
     *   A short string with the coverage percentage.
     */
    public function extractCoverageStats($path)
    {
        if (file_exists($path . '/index.xml')) {
            $data = file_get_contents($path . '/index.xml');
            $xml = simplexml_load_string($data);
            $totals = $xml->project->directory->totals;
            $lines = (string)$totals->lines['percent'];
            $methods = (string)$totals->methods['percent'];
            $classes = (string)$totals->classes['percent'];
            return 'Lines ' . $lines . ' Methods ' . $methods . ' Classes ' . $classes;
        } else {
            return 'Clover report was not found at ' . $path;
        }
    }

}
