describe("Mockup File Uploads", function() {
  function uploadFile() {
    cy.server();
    cy.route("POST", "**/wp-json/projecthuddle/v2/media").as("mediaUpload");

    cy.uploadFile(".ph-file-input", "./test-image.jpg", "image/jpg");

    cy.wait("@mediaUpload").then(xhr => {
      expect(xhr.status).to.eq(201);
      expect(xhr.response.body).to.have.property("id");
      expect(xhr.response.body).to.have.property("media_details");
    });
  }

  // visit mockup page before each test
  beforeEach(function() {
    cy.login();
    cy.visit(
      "http://wordpress-96531-287468.cloudwaysapps.com/mockup/m7i58l/?access_token=d40172557a9591ae82b4ccb1f90f1273"
    );
  });

  it("New comment jpg upload", function() {
    cy.get(".ph-project-image").click({ force: true });
    cy.get(".ph-comment")
      .last()
      .as("comment");

    uploadFile();

    cy.get("@comment")
      .find(".ph-annotation-dot")
      .click({ force: true });
    cy.get("@comment")
      .find(".ql-editor")
      .last()
      .type("Hello, World");

    cy.route("POST", "**/wp-json/projecthuddle/v2/mockup-thread").as(
      "mockupThread"
    );
    cy.get(".ph-button.submit-comment")
      .last()
      .click();
    cy.get(".ph-comment-content")
      .last()
      .contains("Hello, World");

    cy.wait("@mockupThread").then(xhr => {
      expect(xhr.status).to.eq(201);
      expect(xhr.response.body.content).to.have.property(
        "rendered",
        "<p>Hello, World</p>"
      );
      expect(xhr.response.body.comments[0]).to.have.property("attachment_ids");
    });
  });

  it("New empty comment jpg upload", function() {
    cy.get(".ph-project-image").click({ force: true });
    cy.get(".ph-comment")
      .last()
      .as("comment");

    uploadFile();

    cy.server();
    cy.route("POST", "**/wp-json/projecthuddle/v2/mockup-thread").as(
      "mockupThread"
    );

    cy.get("@comment")
      .find(".ql-editor")
      .last()
      .click();
    cy.get("@comment")
      .find(".ph-button.submit-comment")
      .last()
      .click();

    cy.wait("@mockupThread").then(xhr => {
      expect(xhr.status).to.eq(201);
      expect(xhr.response.body.content).to.have.property("rendered");
      expect(xhr.response.body.comments[0]).to.have.property("attachment_ids");
    });
  });
});
