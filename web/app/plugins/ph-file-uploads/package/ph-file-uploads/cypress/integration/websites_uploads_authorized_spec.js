describe("Website File Uploads", function(selector = ".ph-file-input") {
  function uploadFile() {
    cy.server();
    cy.route("POST", "**/wp-json/projecthuddle/v2/media").as("mediaUpload");

    cy.uploadFile(selector, "./test-image.jpg", "image/jpg");

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
      "http://wordpress-96531-287468.cloudwaysapps.com/website/wordpress-96531-287468-cloudwaysapps-com/?access_token=47a4f5c307d9a7abeefad47f383d1cbc"
    );
    cy.iframe(".project-huddle-toolbar", "body").as("toolbar");
    cy.iframe(".project-huddle-panel", "body").as("panel");
  });

  it("New comment jpg upload", function() {
    cy.get("@toolbar")
      .find(".ph-new-comment > .ph-add-comment-text")
      .click({force: true});

    // click new comment
    cy.get("body").click(125, 125);

    // get comment iframe
    cy.iframe(".ph-active-dot .project-huddle-comment-bubble").as("comment");
    cy.get("@comment")
      .find(".ph-file-input")
      .as("fileInput");

    cy.server();
    cy.route("POST", "**/wp-json/projecthuddle/v2/media").as("mediaUpload");

    cy.uploadFile("@fileInput", "./test-image.jpg", "image/jpg");

    cy.get("@comment")
      .find(".ph-button.submit-comment")
      .last()
      .click({force:true});

    // uploadFile('@fileInput');
    //
    // cy.get('@comment').find('.ph-annotation-dot').click({force: true});
    // cy.get('@comment').find('.ql-editor').last().type('Hello, World');
    //
    // cy.route('POST', '**/wp-json/projecthuddle/v2/mockup-thread').as('mockupThread');
    // cy.get('.ph-button.submit-comment').last().click();
    // cy.get('.ph-comment-content').last().contains('Hello, World');
    //
    // cy.wait('@mockupThread').then((xhr) => {
    //   expect(xhr.status).to.eq(201);
    //   expect(xhr.response.body.content).to.have.property('rendered', '<p>Hello, World</p>');
    //   expect(xhr.response.body.comments[0]).to.have.property('attachment_ids');
    // });
  });
});
