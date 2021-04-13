#!/bin/bash

# IMPORTANT: server should be running already.

npm install cypress --save-dev
mkdir cypress && mkdir cypress/integration
cp ./tests/* cypress/integration/

$(npm bin)/cypress run
