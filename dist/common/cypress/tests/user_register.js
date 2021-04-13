describe('Register page', () => {
  it('Registers a new user', () => {
    cy.visit('/')
    cy.contains('Log in').click()
    cy.contains('Create new account').click()
    cy.url().should('include', '/register')
    cy.get('input[name=name]').type('John Doe')
    cy.get('input[name=mail]').type('john.doe@domain.com{enter}')
    cy.contains('Thank you').should('be.visible')
  })
})
