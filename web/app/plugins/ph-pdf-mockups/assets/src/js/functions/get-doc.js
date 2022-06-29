import PDFJS from "pdfjs-dist";
PDFJS.GlobalWorkerOptions.workerSrc = phPdf.workerSrc;

window.phPdfFetched = window.phPdfFetched || {};
window.phPdfFetching = window.phPdfFetching || {};

export default async function(source_url, pagenum = 0) {
  if (!window.phPdfFetching[source_url]) {
    window.phPdfFetching[source_url] = true;
    try {
      window.phPdfFetched[source_url] = await PDFJS.getDocument(source_url)
        .promise;
    } catch (e) {
      console.error(e);
    }
  }

  while (!window.phPdfFetched[source_url]) {
    await new Promise((resolve) => setTimeout(resolve, 200));
  }
  if (pagenum) {
    return await window.phPdfFetched[source_url].getPage(pagenum);
  }

  return window.phPdfFetched[source_url];
}
