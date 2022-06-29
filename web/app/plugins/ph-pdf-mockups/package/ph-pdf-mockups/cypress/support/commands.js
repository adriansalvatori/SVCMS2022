// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This is will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })

Cypress.Commands.add("login", creds => {
  var username = "admin_test";
  var password = "password1339";

  if (creds) {
    if (creds.username) {
      username = creds.username;
    }
    if (creds.password) {
      password = creds.password;
    }
  }

  Cypress.log({
    name: "loginByForm",
    message: username + " | " + password
  });

  cy.request({
    method: "POST",
    url:
      "http://wordpress-96531-287468.cloudwaysapps.com/wp-json/projecthuddle/v2/users/login", // baseUrl will be prepended to this url
    body: {
      username: username,
      password: password
    }
  });
});

/**
 * Uploads a file to an input
 * @memberOf Cypress.Chainable#
 * @name upload_file
 * @function
 * @param {String} selector - element to target
 * @param {String} fileUrl - The file url to upload
 * @param {String} type - content type of the uploaded file
 */
Cypress.Commands.add("uploadFile", (selector, fileUrl, type = "", iframe) => {
  return cy
    .get(selector)
    .last()
    .then(subject => {
      return cy
        .fixture(fileUrl, "base64")
        .then(Cypress.Blob.base64StringToBlob)
        .then(blob => {
          return cy.window().then(win => {
            const el = subject[0];
            const nameSegments = fileUrl.split("/");
            const name = nameSegments[nameSegments.length - 1];
            const testFile = new win.File([blob], name, { type });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(testFile);
            el.files = dataTransfer.files;
            return subject;
          });
        });
    });
});

Cypress.Commands.add("iframe", (selector, element) => {
  return cy
    .get(`${selector || "iframe"}`, { timeout: 10000 })
    .should($iframe => {
      expect($iframe.contents().find(element || "body")).to.exist;
    })
    .then($iframe => {
      var w = cy.wrap($iframe.contents().find("body"));
      // optional - add a class to the body to let the iframe know it's running inside the cypress
      // replaces window.Cypress because window.Cypress does not work from inside the iframe
      w.invoke("addClass", "cypress");
      return w;
    });
});
