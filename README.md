# Drupal 9 CI

![Create release](https://github.com/lullabot/drupal9ci/actions/workflows/create-release/badge.svg)
![Publish Docker image](https://github.com/lullabot/drupal9ci/actions/workflows/publish-docker-image/badge.svg)
![Test](https://github.com/lullabot/drupal9ci/actions/workflows/test/badge.svg)
[![codecov](https://codecov.io/gh/Lullabot/drupal9ci/branch/master/graph/badge.svg?token=akwlXfbC0W)](https://codecov.io/gh/Lullabot/drupal9ci)

This repository provides the foundation to implement [Continuous Integration](https://en.wikipedia.org/wiki/Continuous_integration) in a Drupal 9
project using any of the following CI providers:

 * [CircleCI](#circleci)
 * [Travis CI](#travis-ci)
 * [GitLab CI](#gitlab-ci)
 * [GitHub Actions](#github-actions)
 * [Bitbucket Pipelines](#bitbucket)

If you want to test an individual module instead of a Drupal project, see Andrew Berry's
[drupal_tests](https://github.com/deviantintegral/drupal_tests).

Here is a clip that shows [how it works for CircleCI](https://www.youtube.com/watch?v=wd_5mX0x4K8).

## Requirements

The scripts assume that you have a Drupal 9 project created using [drupal-project](https://github.com/drupal-composer/drupal-project),
which sets a well known foundation. If your project's directory
structure differs from what _drupal-project_ sets up, you will need to
adjust the CI scripts.

It's also recommended to adjust your project to add a subset of the `settings.php` file into
version control and rely on `settings.local.php` for setting the database connection. See [this
commit](https://github.com/juampynr/drupal8-circleci/commit/817d0b6674c42dba73165b047b6b89d72ee72d11)
which contains these changes among other ones. The CI scripts have their own `settings.local.php`
which is copied at build time into `web/sites/default`.

#### DocumentRoot: web vs docroot

By default, Apache and the rest of the code is set up to have the `DocumentRoot` folder as `web`. If
your project uses `docroot` instead, the easiest thing to do is to symlink one to the other. This can be
done adding a line like [this one](https://github.com/Lullabot/drupal9ci/blob/master/dist/bitbucket/RoboFile.php#L187)
before running Apache (you will need to adapt paths).

Alternatively you can go through the files and replace as needed. You can see a list of affected files in
[this comment](https://github.com/Lullabot/drupal9ci/issues/74#issuecomment-884238645).

## Installation

### [CircleCI](https://circleci.com)

[Demo repository](https://github.com/juampynr/drupal8-circleci) | [Deep dive article](https://www.lullabot.com/articles/continuous-integration-drupal-8-circleci)

Download the `drupal9ci` binary from https://github.com/Lullabot/drupal9ci/releases into the project root
and run the command as `./drupal9ci` and select CircleCI. Once complete, continue below to complete the setup.

Sign up at [CircleCI](https://circleci.com/) and allow access to your project's repository.

![CircleCI watch](docs/images/circleci-watch.png)

Happy CI-ing! :-D. From now every time you create a pull request, CircleCI will run the
set of jobs and report their result like in the following screenshot:

![CircleCI pull request](docs/images/circleci-pr.png)

For an overview of the CircleCI features, have a look at
[this article from the Lullabot blog](https://www.lullabot.com/articles/continuous-integration-drupal-8-circleci).

#### Using a custom Docker image

The [CircleCI configuration file](dist/circleci/.circleci/config.yml) uses a
[custom Docker image](https://hub.docker.com/r/juampynr/drupal8ci/) that extends from
the [official Drupal image](https://hub.docker.com/_/drupal/) and it is [hosted at
Docker Hub](https://hub.docker.com/r/juampynr/drupal8ci/). If this image
does not fit your project's architecture then consider [creating your own image](https://circleci.com/docs/2.0/custom-images/)
based out of it.

### [Travis CI](https://travis-ci.org)

[Demo repository](https://github.com/juampynr/drupal8-travis-ci) | [Deep dive article](https://www.lullabot.com/articles/continuous-integration-in-drupal-8-with-travis-ci)

Download the `drupal9ci` binary from https://github.com/Lullabot/drupal9ci/releases into the project root
and run the command as `./drupal9ci` and select Travis CI. Once complete, continue below to complete the setup.

Sign up at [Travis CI](https://travis-ci.com/) and allow access to your project's repository:

![Travis watch](docs/images/travis-watch.png)

Happy CI-ing! :-D. From now on every pull request will trigger a build in Travis and its
progress will be visible like in the following screenshot:

![Travis pull request](docs/images/travis-pr.png)

For you to see the result of the individual jobs, you need to click at the Details link
from the above screenshot:

![Travis CI jobs](docs/images/travis-jobs.png)

#### Setting up code coverage reports

[Coveralls.io](https://coveralls.io/) is a third party tool that can host and present
PHPUnit code coverage reports in a neat way within a pull request. Here is how to set it up:

Register at https://coveralls.io using your GitHub account and then add your repository
like in the following screenshot:

![Coveralls add repository](docs/images/coveralls-add-repo.png)

Then take the chance to adjust a couple settings to get cleaner feedback in pull
requests:

![Coveralls settings](docs/images/coveralls-settings.png)

That's it! Here is a sample report which you can see by clicking at Details
at the pull request's status message:

![Coveralls report](docs/images/coveralls-report.png)


### [GitLab CI](https://about.gitlab.com/features/gitlab-ci-cd/)

[Demo repository](https://gitlab.com/juampynr/drupal8-gitlab) | [Deep dive article](https://www.lullabot.com/articles/installer-drupal-8-and-gitlab-ci)

Download the `drupal9ci` binary from https://github.com/Lullabot/drupal9ci/releases into the project root
and run the command as `./drupal9ci` and select GitLab CI. Once complete, continue below to complete the setup.

Review, commit, and push the resulting changes. After doing that, navigate to the project's homepage
at GitLab and open the CI / CD >> Pipelines section. You should see a running pipeline like
the following one:

![GitLab pipeline](docs/images/gitlab-pipeline.png)

### [GitHub Actions](https://github.com/features/actions)

[Demo repository](https://github.com/juampynr/drupal8-github-actions)

Download the `drupal9ci` binary from https://github.com/Lullabot/drupal9ci/releases into the project root
and run the command as `./drupal9ci` and select GitHub Actions. Once complete, continue below to complete the setup.

Review, commit, and push the resulting changes. After doing that, navigate to the repository's homepage
at GitHub and open the Actions tab. You should see a running workflow like the following one:

![Github actions](docs/images/github-actions.png)


### [Bitbucket](https://support.atlassian.com/bitbucket-cloud/docs/get-started-with-bitbucket-pipelines)

Download the `drupal9ci` binary from https://github.com/Lullabot/drupal9ci/releases into the project root
and run the command as `./drupal9ci` and select Bitbucket. Once complete, continue below to complete the setup.

Review, commit, and push the resulting changes. After doing that, navigate to the repository's homepage
at Bitbucket and open the Pipelines tab. You should see a running workflow like the following one:

![Bitbucket pipelines](docs/images/bitbucket.png)

### Setting up the Behat and Cypress jobs for all platforms

The Behat and Cypress jobs require a running Drupal 9 site. The repository contains the code, but for running
tests in a realistic environment you need:

##### 1. A recent copy of the production environment's database

There are several ways to accomplish this:

**Using a prepopulated Docker database image**

See [Achieve Rocketship-Fast Jobs in CircleCI by Preinstalling the Database](https://www.lullabot.com/articles/rocket-ship-fast-jobs-circleci-preinstalling-database)

**Travis**

If you have Drush site aliases, and your repository is private, then follow these
instructions to [add an SSH key](https://docs.travis-ci.com/user/private-dependencies/#User-Key).
Next, set up a drush site alias. Finally, adjust the Behat job to run `drush @my.alias sql-cli`.

**CircleCI**

If you have Drush site aliases, then at the CircleCI dashboard go to the project's permissions
and add an SSH key. Next, add `drush @my.alias sql-cli` to the Behat job at `.circleci/config.yml`.

**Alternative**

Alternatively, upload a [sanitized](https://drushcommands.com/drush-8x/sql/sql-sanitize/) database
dump somewhere and set up the `DB_DUMP_URL` environment variable so the job can download it.

For example:
![Travis CI db env var](docs/images/travisci-db-var.png)
![CircleCI database via environment variable](docs/images/circleci-db-env.png)

A sample implementation is to use Dropbox API. [See this repository for further details](https://github.com/juampynr/dropbox-api).

##### 2. The production environment's files directory

If you have a site alias, then add `drush rsync @my.alias @self` to the Behat job. Alternatively,
use [Stage File Proxy](https://www.drupal.org/project/stage_file_proxy) module.
