describe('Register page', () => {
  it('Registers a new user', () => {
    const id = Date.now().toString()

    cy.visit('/user/register')
    cy.get('input[name=name]').type(`john_doe_${id}`)
    cy.get('input[name=mail]').type(`john_doe_${id}@domain.com`)
    cy.get('#edit-submit').click()
    cy.contains('Thank you').should('be.visible')
  })
})
