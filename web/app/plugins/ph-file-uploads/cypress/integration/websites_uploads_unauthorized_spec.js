describe("Website File Uploads", function() {
  // visit mockup page before each test
  beforeEach(function() {
    cy.visit(
      "http://wordpress-96531-287468.cloudwaysapps.com/website/wordpress-96531-287468-cloudwaysapps-com/?access_token=47a4f5c307d9a7abeefad47f383d1cbc"
    );
    cy.iframe(".project-huddle-toolbar", "body").as("toolbar");
    cy.iframe(".project-huddle-panel", "body").as("panel");
  });

  it("Can't upload unless logged in", function() {
    cy.get("@toolbar")
      .find(".ph-new-comment > .ph-add-comment-text")
      .click();

    // click new comment
    cy.get("body").click(125, 125);

    // get comment ifram
    cy.iframe(".ph-active-dot .project-huddle-comment-bubble").as("comment");

    cy.get("@comment")
      .find(".ph-upload")
      .should("not.exist");
  });
});
