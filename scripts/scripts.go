package scripts

import _ "embed"

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

func LoadSetupScripts() *SetupScripts {
	return &SetupScripts{
		BitBucket:     setupBitbucket,
		CircleCI:      setupCircleCI,
		GitHubActions: setupGitHubActions,
		GitLabCI:      setupGitLabCI,
		TravisCI:      setupTravisCI,
	}
}
