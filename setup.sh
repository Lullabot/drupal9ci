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
	# Turn on error detection and trap errors.
	trap "echoerr 'An unexpected error was encountered. Installation failed.'" ERR
	set -e

	# Download and extract CircleCI configuration and sample tests.
	wget -O "$tmpdir/master.zip" https://github.com/juampynr/drupal8ci/archive/master.zip
	unzip "$tmpdir/master.zip" 'drupal8ci-master/dist/*' -d "$tmpdir"
	rsync -va --ignore-existing "$tmpdir/drupal8ci-master/dist/" .

	# Add development dependencies to run the CircleCI jobs.
	#
	# behat/mink-extension is pinned until https://github.com/Behat/MinkExtension/pull/311 gets fixed.
	all_dev_deps=(
		cweagans/composer-patches
		behat/mink-extension:v2.2
		behat/mink-selenium2-driver:^1.3
		bex/behat-screenshot
		drupal/coder:^8.2
		drupal/drupal-extension:master-dev
		drush/drush:~8.1
		guzzlehttp/guzzle:^6.0@dev
	)
	# Find out what packages are already required.
	existing_packages=`composer show -ND`
	# Determine which of these dependencies are not already installed by using
	# uniq -u. We don't want to re-install existing dependencies as require-dev.
	# We also don't want any of the items in $existing_packages to show up here so
	# we echo those packages twice to ensure they don't. In other words, the only
	# packages that would end up in $dev_deps are just ones that only occur once.
	dev_deps=`echo "${all_dev_deps[@]%:*} $existing_packages $existing_packages" |
		tr ' ' '\n' |
		sort |
		uniq -u`

	# Only run composer install if we found some packages to install.
	if [[ -n $dev_deps ]]; then
		# Now find the dev dependencies with versions above from the $all_dev_deps
		# array. We do this by printing out the array as multiline, and passing it
		# to grep, checking for the presence of any of the items in $dev_deps.
		dev_deps_to_install=`printf '%s\n' "${all_dev_deps[@]}" | grep -E "(${dev_deps//[[:space:]]/|})"`
		composer require --dev $dev_deps_to_install
	fi
}

#######################################
# Helper function to output a string to stderr and exit.
#######################################
echoerr() {
	echo "$@" 1>&2
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
