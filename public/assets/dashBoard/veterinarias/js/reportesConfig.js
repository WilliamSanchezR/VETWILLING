document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    window.REPORTES_API_URL = body.dataset.reportesApiUrl || '';
    window.REPORTES_PDF_URL = body.dataset.reportesPdfUrl || '';
});
