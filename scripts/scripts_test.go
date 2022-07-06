package scripts

import (
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestMapCIProviderToScript(t *testing.T) {
	type testCase struct {
		name         string
		ciProvider   func() *string
		setupScripts *SetupScripts
		assertions   func(t *testing.T, tt testCase)
	}
	tests := []testCase{
		{
			name: "wrong provider",
			ciProvider: func() *string {
				return nil
			},
			setupScripts: LoadSetupScripts(),
			assertions: func(t *testing.T, tt testCase) {
				ciProvider, err := MapCIProviderToScript(tt.ciProvider(), tt.setupScripts)
				assert.Nil(t, ciProvider)
				assert.Error(t, err)
			},
		},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			tt.assertions(t, tt)
		})
	}
}
