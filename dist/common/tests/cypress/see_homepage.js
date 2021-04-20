describe('Visit homepage', () => {
  it('Sees welcome message', () => {
    cy.visit('/')
    cy.contains('Welcome').should('be.visible')
  })
})
