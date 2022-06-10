<?php
// @codingStandardsIgnoreStart
use Robo\Exception\TaskException;

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
     * The database URL.
     */
    const DB_URL = 'sqlite://tmp/site.sqlite';

    /**
     * Base path where the web files will be.
     */
    const APACHE_PATH = '/var/html/www';

    /**
     * Mount path where the web files will be.
     */
    const MOUNT_PATH = '/opt/drupal';

    /**
     * RoboFile constructor.
     */
    public function __construct()
    {
        // Treat this command like bash -e and exit as soon as there's a failure.
        $this->stopOnFail();
    }

    /**
     * Command to run unit tests.
     *
     * @return \Robo\Result
     *   The result of the collection of tasks.
     */
    public function jobRunUnitTests()
    {
        $collection = $this->collectionBuilder();
        $collection->addTaskList($this->buildEnvironment());
        $collection->addTaskList($this->serveDrupal());
        $collection->addTask($this->waitForDrupal());
        $collection->addTaskList($this->importDatabase());
        $collection->addTaskList($this->runUpdatePath());
        $collection->addTaskList($this->runUnitTests());
        return $collection->run();
    }

    /**
     * Command to check coding standards.
     *
     * @return null|\Robo\Result
     *   The result of the set of tasks.
     *
     * @throws \Robo\Exception\TaskException
     */
    public function jobCheckCodingStandards()
    {
        return $this->taskExecStack()
            ->stopOnFail()
            ->exec('vendor/bin/phpcs --standard=Drupal web/modules/custom')
            ->exec('vendor/bin/phpcs --standard=DrupalPractice web/modules/custom')
            ->run();
    }

    /**
     * Command to run behat tests.
     *
     * @return \Robo\Result
     *   The resul tof the collection of tasks.
     */
    public function jobRunBehatTests()
    {
        $collection = $this->collectionBuilder();
        $collection->addTaskList($this->buildEnvironment());
        $collection->addTaskList($this->serveDrupal());
        $collection->addTask($this->waitForDrupal());
        $collection->addTaskList($this->importDatabase());
        $collection->addTaskList($this->runUpdatePath());
        $collection->addTaskList($this->runBehatTests());
        return $collection->run();
    }

    /**
     * Command to run Cypress tests.
     *
     * @return \Robo\Result
     *   The result tof the collection of tasks.
     */
    public function jobRunCypressTests()
    {
        $collection = $this->collectionBuilder();
        $collection->addTaskList($this->buildEnvironment());
        $collection->addTaskList($this->serveDrupal());
        $collection->addTask($this->waitForDrupal());
        $collection->addTaskList($this->importDatabase());
        $collection->addTaskList($this->runUpdatePath());
        $collection->addTaskList($this->runCypressTests());
        return $collection->run();
    }

    /**
     * Builds the Docker environment.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function buildEnvironment()
    {
        $force = TRUE;
        $tasks = [];
        $tasks[] = $this->taskFilesystemStack()
            ->copy('.travis/docker-compose.yml', 'docker-compose.yml', $force)
            ->copy('.travis/php-node.dockerfile', 'php-node.dockerfile', $force)
            ->copy('.travis/config/settings.local.php', 'web/sites/default/settings.local.php', $force)
            ->copy('.travis/config/behat.yml', 'tests/behat.yml', $force)
            ->copy('.travis/config/phpunit.xml', 'web/core/phpunit.xml', $force)
            ->copy('.cypress/cypress.json', 'cypress.json', $force)
            ->copy('.cypress/package.json', 'package.json', $force);
        $tasks[] = $this->taskExec('docker-compose pull');
        $tasks[] = $this->taskExec('docker-compose up -d');
        return $tasks;
    }

    /**
     * Waits for Drupal to accept requests.
     *
     * @TODO Find an efficient way to wait for Drupal.
     *
     * @return \Robo\Task\Base\Exec
     *   A task to check that Drupal is ready.
     */
    protected function waitForDrupal()
    {
        return $this->taskExec('sleep 30s');
    }

    /**
     * Updates the database.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runUpdatePath()
    {
        $tasks = [];
        $tasks[] = $this->taskDockerComposeExec('vendor/bin/drush --yes updatedb');
        $tasks[] = $this->taskDockerComposeExec('vendor/bin/drush --yes config-import');
        $tasks[] = $this->taskDockerComposeExec('vendor/bin/drush cr');
        return $tasks;
    }

    /**
     * Run docker-compose task on php container.
     */
    protected function taskDockerComposeExec($command, $mount_path = TRUE) {
        $command = ($mount_path) ?
            "cd " . static::MOUNT_PATH . " && {$command}" :
            $command;
        return $this->taskExec("docker-compose exec -T php bash -c '{$command}'");
    }

    /**
     * Serves Drupal.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function serveDrupal()
    {
        $tasks = [];
        $tasks[] = $this->taskDockerComposeExec('rm -rf ' . static::APACHE_PATH);
        $tasks[] = $this->taskDockerComposeExec('mkdir -p ' . dirname(static::APACHE_PATH));
        $tasks[] = $this->taskDockerComposeExec('chown -R www-data:www-data ' . static::MOUNT_PATH);
        $tasks[] = $this->taskDockerComposeExec('ln -sf ' . static::MOUNT_PATH . '/web ' . static::APACHE_PATH);
        $tasks[] = $this->taskDockerComposeExec('echo "\nServerName localhost" >> /etc/apache2/apache2.conf');
        $tasks[] = $this->taskDockerComposeExec('service apache2 start');
        return $tasks;
    }

    /**
     * Imports and updates the database.
     *
     * This task assumes that there is an environment variable $DB_DUMP_URL
     * that contains a URL to a database dump. Ideally, you should set up drush
     * site aliases and then replace this task by a drush sql-sync one. See the
     * README at lullabot/drupal9ci for further details.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function importDatabase()
    {
        $tasks = [];
        $tasks[] = $this->taskDockerComposeExec('mysql -u root -h mariadb -e "create database if not exists drupal"');
        $tasks[] = $this->taskDockerComposeExec('wget -O /tmp/dump.sql "' . getenv('DB_DUMP_URL') . '"');
        $tasks[] = $this->taskDockerComposeExec('vendor/bin/drush sql-cli < /tmp/dump.sql');
        return $tasks;
    }

    /**
     * Run unit tests.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runUnitTests()
    {
        $tasks = [];
        $tasks[] = $this->taskDockerComposeExec('vendor/bin/phpunit -c web/core --verbose web/modules/custom');
        return $tasks;
    }

    /**
     * Runs Behat tests.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runBehatTests()
    {
        $tasks = [];
        $tasks[] = $this->taskDockerComposeExec('vendor/bin/behat --verbose -c tests/behat.yml');
        return $tasks;
    }

    /**
     * Runs Cypress tests.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runCypressTests()
    {
        $tasks = [];
        $tasks[] = $this->taskDockerComposeExec('npm install cypress@9 --save-dev --unsafe-perm');
        $tasks[] = $this->taskDockerComposeExec('$(npm bin)/cypress run');
        return $tasks;
    }

    /**
     * Get the absolute path to the docroot.
     *
     * @return string
     *   The document root.
     */
    protected function getDocroot()
    {
        return (getcwd());
    }

}
