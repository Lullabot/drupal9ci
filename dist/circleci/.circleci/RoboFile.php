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
     * Installs composer dependencies.
     */
    public function installDependencies()
    {
        $this->taskComposerInstall()
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
    )
    {
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

}
