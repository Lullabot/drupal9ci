package scripts

import (
	_ "embed"
	"fmt"
)

const (
	Bitbucket     = "Bitbucket"
	CircleCI      = "CircleCI"
	GithubActions = "GitHub Actions"
	GitLabCI      = "GitLab CI"
	TravisCI      = "Travis CI"
)

//go:embed setup-bitbucket.sh
var setupBitbucket string

//go:embed setup-circleci.sh
var setupCircleCI string

//go:embed setup-github-actions.sh
var setupGitHubActions string

//go:embed setup-gitlab-ci.sh
var setupGitLabCI string

//go:embed setup-travis-ci.sh
var setupTravisCI string

type SetupScripts struct {
	BitBucket     string
	CircleCI      string
	GitHubActions string
	GitLabCI      string
	TravisCI      string
}

func GetCIProviderList() []string {
	return []string{
		Bitbucket,
		CircleCI,
		GithubActions,
		GitLabCI,
		TravisCI,
	}
}

func LoadSetupScripts() *SetupScripts {
	return &SetupScripts{
		BitBucket:     setupBitbucket,
		CircleCI:      setupCircleCI,
		GitHubActions: setupGitHubActions,
		GitLabCI:      setupGitLabCI,
		TravisCI:      setupTravisCI,
	}
}

func MapCIProviderToScript(ciProvider *string, setupScripts *SetupScripts) (*string, error) {
	var setupScript *string
	var err error

	if ciProvider == nil {
		err = fmt.Errorf("Missing CI provider")
	} else {
		switch *ciProvider {
		case Bitbucket:
			setupScript = &setupScripts.BitBucket
		case CircleCI:
			setupScript = &setupScripts.CircleCI
		case GithubActions:
			setupScript = &setupScripts.GitHubActions
		case GitLabCI:
			setupScript = &setupScripts.GitLabCI
		case TravisCI:
			setupScript = &setupScripts.TravisCI
		default:
			err = fmt.Errorf("Unknown CI provider")
		}
	}
	
	return setupScript, err
}
