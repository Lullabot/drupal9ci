package cmd

import (
	"drupal9ci/scripts"
	"fmt"
	"github.com/manifoldco/promptui"
	"github.com/spf13/cobra"
	"github.com/spf13/viper"
	"io"
	"os"
	"os/exec"
	"strings"
)

var cfgFile string

var rootCmd = &cobra.Command{
	Use:   "drupal9ci",
	Short: "An interactive command to add Continuous Integration to a Drupal project",
}

func Execute(setupScripts *scripts.SetupScripts) {
	selectedCIProvider, err := getCIProvider(os.Args)
	if err != nil {
		fmt.Printf(err.Error())
		return
	}

	setupScript, err := scripts.MapCIProviderToScript(selectedCIProvider, setupScripts)
	if err != nil {
		fmt.Printf(err.Error())
		return
	}

	fmt.Println("This might take a few seconds...")

	res, err := executeCIInstallerScript(setupScript)
	if err != nil {
		fmt.Println("error executing script: ", err.Error())
	}
	fmt.Println("output: ", string(res))
}

func executeCIInstallerScript(setupScript *string) ([]byte, error) {
	stringReader := strings.NewReader(*setupScript)
	stringReadCloser := io.NopCloser(stringReader)
	execScriptCmd := exec.Command("bash")

	execScriptCmd.Stdin = stringReadCloser
	return execScriptCmd.CombinedOutput()
}

func getCIProvider(args []string) (*string, error) {
	if len(args) > 1 {
		return &args[1], nil
	}

	prompt := promptui.Select{
		Label: "Select CI provider",
		Items: scripts.GetCIProviderList(),
	}

	_, ciProvider, err := prompt.Run()
	if err != nil {
		return nil, fmt.Errorf("Prompt failed %s", err.Error())
	}
	return &ciProvider, nil
}

func init() {
	cobra.OnInitialize(initConfig)

	// Here you will define your flags and configuration settings.
	// Cobra supports persistent flags, which, if defined here,
	// will be global for your application.

	rootCmd.PersistentFlags().StringVar(&cfgFile, "config", "", "config file (default is $HOME/.drupal9ci.yaml)")

	// Cobra also supports local flags, which will only run
	// when this action is called directly.
	rootCmd.Flags().BoolP("toggle", "t", false, "Help message for toggle")
}

// initConfig reads in config file and ENV variables if set.
func initConfig() {
	if cfgFile != "" {
		// Use config file from the flag.
		viper.SetConfigFile(cfgFile)
	} else {
		// Find home directory.
		home, err := os.UserHomeDir()
		cobra.CheckErr(err)

		// Search config in home directory with name ".drupal9ci" (without extension).
		viper.AddConfigPath(home)
		viper.SetConfigType("yaml")
		viper.SetConfigName(".drupal9ci")
	}

	viper.AutomaticEnv() // read in environment variables that match

	// If a config file is found, read it in.
	if err := viper.ReadInConfig(); err == nil {
		fmt.Fprintln(os.Stderr, "Using config file:", viper.ConfigFileUsed())
	}
}
