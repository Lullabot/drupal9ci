## Cypress CI setup.

`package.json` is empty by default to avoid linking front end dependencies
to e2e testing. It is recommended that you only add here e2e related
dependencies. If you want to test locally, you can copy this file to the
root of the repo (if you don't have one already) and just run locally
`npm install cypress@9 --save-dev`.

`cypress.json` is set up to work with the CI integration. If you want to
test locally, copy this file to the root of the repo and tweak any of the
values if needed (ie: `baseUrl`).

Once everything is set up locally, you can run the tests like this:
`$(npm bin)/cypress open` or `$(npm bin)/cypress run`.
