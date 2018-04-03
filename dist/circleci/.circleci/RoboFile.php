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
     * The database URL.
     *
     * @var string
     */
    const DB_URL = 'mysql://root@127.0.0.1/drupal8';

    /**
     * Command to run unit tests.
     *
     * @return \Robo\Result
     *   The result of the collection of tasks.
     */
    public function jobRunUnitTests()
    {
        $collection = $this->collectionBuilder();
        $collection->addTask($this->installDependencies());
        $collection->addTask($this->waitForDatabase());
        $collection->addTask($this->installDrupal());
        $collection->addTaskList($this->runUnitTests());
        return $collection->run();
    }

    /**
     * Command to generate a coverage report.
     *
     * @return \Robo\Result
     *   The result of the collection of tasks.
     */
    public function jobGenerateCoverageReport()
    {
        $collection = $this->collectionBuilder();
        $collection->addTask($this->installDependencies());
        $collection->addTask($this->waitForDatabase());
        $collection->addTask($this->installDrupal());
        $collection->addTaskList($this->runUnitTestsWithCoverage());
        return $collection->run();
    }

    /**
     * Command to check for Drupal's Coding Standards.
     *
     * @return \Robo\Result
     *   The result of the collection of tasks.
     */
    public function jobCheckCodingStandards()
    {
        $collection = $this->collectionBuilder();
        $collection->addTask($this->installDependencies());
        $collection->addTaskList($this->runCodeSniffer());
        return $collection->run();
    }

    /**
     * Command to run behat tests.
     *
     * @return \Robo\Result
     *   The result tof the collection of tasks.
     */
    public function jobRunBehatTests()
    {
        $collection = $this->collectionBuilder();
        $collection->addTask($this->installDependencies());
        $collection->addTask($this->waitForDatabase());
        $collection->addTaskList($this->importDatabase());
        $collection->addTaskList($this->runUpdatePath());
        $collection->addTaskList($this->runBehatTests());
        return $collection->run();
    }

    /**
     * Imports and updates the database.
     *
     * This task assumes that there is an environment variable $DB_DUMP_URL
     * that contains a URL to a database dump. Ideally, you should set up drush
     * site aliases and then replace this task by a drush sql-sync one. See the
     * README at lullabot/drupal8ci for further details.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function importDatabase()
    {
        $force = true;
        $tasks = [];
        $tasks[] = $this->taskExec('mysql -u root -h 127.0.0.1 -e "create database drupal8"');
        $tasks[] = $this->taskFilesystemStack()
            ->copy('.circleci/config/settings.local.php', 'web/sites/default/settings.local.php', $force);
        $tasks[] = $this->taskExec('wget -O dump.sql ' . getenv('DB_DUMP_URL'));
        $tasks[] = $this->drush()->rawArg('sql-cli < dump.sql');
        return $tasks;
    }

    /**
     * Updates the database.
     *
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runUpdatePath()
    {
        $tasks = [];
        $tasks[] = $this->drush()->args('updatedb')->option('yes')->option('verbose');
        $tasks[] = $this->drush()->args('config-import')->option('yes')->option('verbose');
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
        $force = true;
        $tasks = [];
        $tasks[] = $this->taskExec('service apache2 start');
        $tasks[] = $this->taskFilesystemStack()
            ->copy('.circleci/config/behat.yml', 'tests/behat.yml', $force);
        $tasks[] = $this->taskExec('vendor/bin/behat --verbose -c tests/behat.yml');
        return $tasks;
    }

    /**
     * Installs composer dependencies.
     *
     * @return \Robo\Contract\TaskInterface
     *   A task instance.
     */
    protected function installDependencies()
    {
        return $this->taskComposerInstall()
            ->optimizeAutoloader();
    }

    /**
     * Waits for the database service to be ready.
     *
     * @return \Robo\Contract\TaskInterface
     *   A task instance.
     */
    protected function waitForDatabase()
    {
        return $this->taskExec('dockerize -wait tcp://localhost:3306 -timeout 1m');
    }

    /**
     * Install Drupal.
     *
     * @return \Robo\Task\Base\Exec
     *   A task to install Drupal.
     */
    protected function installDrupal()
    {

        $task = $this->drush()
            ->args('site-install')
            ->option('verbose')
            ->option('yes')
            ->option('db-url', static::DB_URL, '=');
        return $task;
    }

    /**
     * Run unit tests.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runUnitTests()
    {
        $force = true;
        $tasks = [];
        $tasks[] = $this->taskFilesystemStack()
            ->copy('.circleci/config/phpunit.xml', 'web/core/phpunit.xml', $force)
            ->mkdir('artifacts/phpunit', 777);
        $tasks[] = $this->taskExecStack()
            ->dir('web')
            ->exec('../vendor/bin/phpunit -c core --debug --verbose --log-junit ../artifacts/phpunit/phpunit.xml modules/custom');
        return $tasks;
    }

    /**
     * Run unit tests and generates a code coverage report.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runUnitTestsWithCoverage()
    {
        $force = true;
        $tasks = [];
        $tasks[] = $this->taskFilesystemStack()
            ->copy('.circleci/config/phpunit.xml', 'web/core/phpunit.xml', $force)
            ->mkdir('artifacts/coverage-xml', 777)
            ->mkdir('artifacts/coverage-html', 777);
        $tasks[] = $this->taskExecStack()
            ->dir('web')
            ->exec('../vendor/bin/phpunit -c core --debug --verbose --coverage-xml ../artifacts/coverage-xml --coverage-html ../artifacts/coverage-html modules/custom');
        return $tasks;
    }

    /**
     * Sets up and runs code sniffer.
     *
     * @return \Robo\Task\Base\Exec[]
     *   An array of tasks.
     */
    protected function runCodeSniffer()
    {
        $tasks = [];
        $tasks[] = $this->taskExecStack()
            ->exec('vendor/bin/phpcs --config-set installed_paths vendor/drupal/coder/coder_sniffer');
        $tasks[] = $this->taskFilesystemStack()
            ->mkdir('artifacts/phpcs');
        $tasks[] = $this->taskExecStack()
            ->exec('vendor/bin/phpcs --standard=Drupal --report=junit --report-junit=artifacts/phpcs/phpcs.xml web/modules/custom')
            ->exec('vendor/bin/phpcs --standard=DrupalPractice --report=junit --report-junit=artifacts/phpcs/phpcs.xml web/modules/custom');
        return $tasks;
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

}
