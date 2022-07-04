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

// rootCmd represents the base command when called without any subcommands
var rootCmd = &cobra.Command{
	Use:   "drupal9ci",
	Short: "A brief description of your application",
	Long: `A longer description that spans multiple lines and likely contains
examples and usage of using your application. For example:

Cobra is a CLI library for Go that empowers applications.
This application is a tool to generate the needed files
to quickly create a Cobra application.`,
	// Uncomment the following line if your bare application
	// has an action associated with it:
	// Run: func(cmd *cobra.Command, args []string) { },
}

// Execute adds all child commands to the root command and sets flags appropriately.
// This is called by main.main(). It only needs to happen once to the rootCmd.
func Execute(setupScripts *scripts.SetupScripts) {
	var selectedCIProvider string
	var setupScript string
	var err error

	if len(os.Args) > 1 {
		selectedCIProvider = os.Args[1]
	} else {
		ciProviders := []string{"Bitbucket", "CircleCI", "GitHub Actions", "GitLab CI", "Travis CI"}

		prompt := promptui.Select{
			Label: "Select CI provider",
			Items: ciProviders,
		}

		_, selectedCIProvider, err = prompt.Run()
		if err != nil {
			fmt.Printf("Prompt failed %v\n", err)
			return
		}
	}

	switch selectedCIProvider {
	case "Bitbucket":
		setupScript = setupScripts.BitBucket
	case "CircleCI":
		setupScript = setupScripts.CircleCI
	case "GitHub Actions":
		setupScript = setupScripts.GitHubActions
	case "GitLab CI":
		setupScript = setupScripts.GitLabCI
	case "Travis CI":
		setupScript = setupScripts.TravisCI
	default:
		fmt.Println("Unknown CI provider")
		return
	}

	stringReader := strings.NewReader(setupScript)
	stringReadCloser := io.NopCloser(stringReader)
	execScriptCmd := exec.Command("bash")

	execScriptCmd.Stdin = stringReadCloser
	res, err := execScriptCmd.CombinedOutput()
	if err != nil {
		fmt.Println("error executing script: ", err.Error())
	}
	fmt.Println("output: ", string(res))
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
