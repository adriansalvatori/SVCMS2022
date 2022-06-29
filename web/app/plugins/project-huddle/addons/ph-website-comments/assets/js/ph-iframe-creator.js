/**
 * Create iframe container
 */
var iframe = document.createElement('iframe');

// set iframe container style and attributes
iframe.setAttribute('id', '_PH_frame');
iframe.setAttribute('name', '_PH_frame');
iframe.setAttribute("style", "display: none;");
iframe.setAttribute("data-html2canvas-ignore", true);

// add to iframe container to document
document.body.appendChild(iframe);

// add subframe to iframe
var doc = iframe.contentWindow.document;
doc.open();
doc.write(PH_Website.container);
doc.close();

// Avoid webkit bug which scrolls infinite to the top margin of the iframe
iframe.contentWindow.scroll(0,0);