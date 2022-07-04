package main

import (
	"drupal9ci/cmd"
	"drupal9ci/scripts"
	_ "embed"
)

func main() {
	cmd.Execute(scripts.LoadSetupScripts())
}
