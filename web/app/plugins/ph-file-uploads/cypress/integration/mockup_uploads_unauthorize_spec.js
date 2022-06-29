describe("Mockup File Uploads (Not Logged In)", function() {
  // visit mockup page before each test
  beforeEach(function() {
    cy.visit(
      "http://wordpress-96531-287468.cloudwaysapps.com/mockup/m7i58l/?access_token=d40172557a9591ae82b4ccb1f90f1273"
    );
  });

  it("Can't upload unless logged in", function() {
    // uploads icon should not appear if not logged in
    cy.get(".ph-project-image").click({ force: true });
    cy.get(".ph-comment")
      .last()
      .as("unsavedComment");
    cy.get("@unsavedComment")
      .find(".ph-upload")
      .should("not.exist");
  });
});
