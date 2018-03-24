#!/usr/bin/env bash

#######################################
# Installation script to do the heavy lifting.
#
# We put this inside of a function to avoid any issues that might arise by
# piping this script to bash. Ideally you should avoid piping scripts to bash.
# If you'd like to install without this script, here's where to look:
#######################################
drupal8ci_install() {
	check_dependencies

	# Create a temporary directory for installation.
	tmpdir=`mktemp -d`
	# Now that we've created a temp dir, clean up after ourselves on exit.
	trap "cleanup $tmpdir" EXIT

	# Turn on xtracing and error detection so users know what's happening.
	set -ex
	# Download and extract GitLab CI configuration and sample tests.
	wget -O "$tmpdir/master.zip" https://github.com/lullabot/drupal8ci/archive/master.zip
	unzip "$tmpdir/master.zip" 'drupal8ci-master/dist/gitlabci/*' -d "$tmpdir"
	rsync -va --ignore-existing "$tmpdir/drupal8ci-master/dist/gitlabci/" .
    unzip "$tmpdir/master.zip" 'drupal8ci-master/dist/common/*' -d "$tmpdir"
	rsync -va --ignore-existing "$tmpdir/drupal8ci-master/dist/common/" .

	# Add development dependencies to run the GitLab CI jobs.
	#
	# behat/mink-extension is pinned until https://github.com/Behat/MinkExtension/pull/311 gets fixed.
	composer require --dev \
		behat/mink-extension:v2.2 \
		behat/mink-selenium2-driver:^1.3 \
		bex/behat-screenshot \
		drupal/coder:^8.2 \
		drupal/drupal-extension:master-dev
}

#######################################
# Helper function to output a string to stderr and exit.
#######################################
echoerr() {
	echo "$@" 1>&2;
	exit 23
}

#######################################
# Ensure we have a proper environment for installation.
#######################################
check_dependencies() {
	hash composer ||
		echoerr "You must have composer for this install script to work."

	# Ensure this is a Composer managed Drupal project.
	composer config repositories | grep packages.drupal.org > /dev/null 2>&1 ||
		echoerr "This does not appear to be a Composer managed Drupal project."

	# Verify certain packages exist.
	hash wget ||
		echoerr "You must have wget for this install script to work."

	hash unzip ||
		echoerr "You must have unzip for this install script to work."
}

#######################################
# Helper function to use with trap to clean up after exit.
# Arguments:
#   * param1: The temporary directory to delete.
#######################################
cleanup() {
	echo "Removing $1."
	rm -r $1
}

drupal8ci_install
