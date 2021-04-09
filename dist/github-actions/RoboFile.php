<?php

// @codingStandardsIgnoreStart

/**
 * Base tasks for setting up a module to test within a full Drupal environment.
 *
 * @class RoboFile
 * @codeCoverageIgnore
 */
class RoboFile extends \Robo\Tasks {

  /**
   * Command to build the environment
   *
   * @return \Robo\Result
   *   The result of the collection of tasks.
   */
  public function jobBuild() {
    $collection = $this->collectionBuilder();
    $collection->addTaskList($this->copyConfigurationFiles());
    $collection->addTaskList($this->runComposer());
    return $collection->run();
  }

  /**
   * Command to run unit tests.
   *
   * @return \Robo\Result
   *   The result of the collection of tasks.
   */
  public function jobUnitTests() {
    $collection = $this->collectionBuilder();
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
  public function jobCoverageReport() {
    $collection = $this->collectionBuilder();
    $collection->addTask($this->installDrupal());
    $collection->addTaskList($this->runCoverageReport());
    return $collection->run();
  }

  /**
   * Command to check for Drupal's Coding Standards.
   *
   * @return \Robo\Result
   *   The result of the collection of tasks.
   */
  public function jobCodingStandards() {
    $collection = $this->collectionBuilder();
    $collection->addTaskList($this->runCodeSniffer());
    return $collection->run();
  }

  /**
   * Command to run Chrome headless.
   *
   * @return \Robo\Result
   *   The result tof the task
   */
  public function runChromeHeadless() {
    return $this->taskExec('google-chrome-unstable --disable-gpu --headless --no-sandbox --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222')->run();
  }

  /**
   * Updates the database.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function runUpdateDatabase() {
    $tasks = [];
    $tasks[] = $this->drush()
      ->args('updatedb')
      ->option('yes')
      ->option('verbose');
    $tasks[] = $this->drush()
      ->args('config:import')
      ->option('yes')
      ->option('verbose');
    $tasks[] = $this->drush()->args('cache:rebuild')->option('verbose');
    return $tasks;
  }

  /**
   * Run unit tests.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function runUnitTests() {
    $tasks = [];
    $tasks[] = $this->taskFilesystemStack()
      ->copy('.github/config/phpunit.xml', 'web/core/phpunit.xml', $force);
    $tasks[] = $this->taskExecStack()
      ->dir('web')
      ->exec('../vendor/bin/phpunit -c core --debug --coverage-clover ../build/logs/clover.xml --verbose modules/custom');
    return $tasks;
  }

  /**
   * Generates a code coverage report.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function runCoverageReport() {
    $tasks = [];
    $tasks[] = $this->taskFilesystemStack()
      ->copy('.github/config/phpunit.xml', 'web/core/phpunit.xml', $force);
    $tasks[] = $this->taskExecStack()
      ->dir('web')
      ->exec('../vendor/bin/phpunit -c core --debug --verbose --coverage-html ../coverage modules/custom');
    return $tasks;
  }

  /**
   * Sets up and runs code sniffer.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function runCodeSniffer() {
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
  protected function drush() {
    return $this->taskExec('vendor/bin/drush');
  }

  /**
   * Copies configuration files.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function copyConfigurationFiles() {
    $force = TRUE;
    $tasks = [];
    $tasks[] = $this->taskFilesystemStack()
      ->copy('.github/config/settings.local.php',
        'web/sites/default/settings.local.php', $force)
      ->copy('.github/config/.env',
        '.env', $force);
    return $tasks;
  }

  /**
   * Runs composer commands.
   *
   * @return \Robo\Task\Base\Exec[]
   *   An array of tasks.
   */
  protected function runComposer() {
    $tasks = [];
    $tasks[] = $this->taskComposerValidate()->noCheckPublish();
    $tasks[] = $this->taskComposerInstall()
      ->noInteraction()
      ->envVars(['COMPOSER_ALLOW_SUPERUSER' => 1, 'COMPOSER_DISCARD_CHANGES' => 1] + getenv())
      ->optimizeAutoloader();
    return $tasks;
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
          ->option('yes');
      return $task;
  }

}
