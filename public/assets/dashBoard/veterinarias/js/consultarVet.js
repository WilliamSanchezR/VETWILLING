document.addEventListener('DOMContentLoaded', function () {
    const btnRutaReporte = document.getElementById('btnRutaReporte');
    if (!btnRutaReporte || typeof DataTable === 'undefined') {
        return;
    }

    const rutaReporte = btnRutaReporte.dataset.ruta || '';

    new DataTable('#tablaCitas', {
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="bi bi-clipboard"></i> Copy',
                        className: 'dt-button-custom'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="bi bi-filetype-csv"></i> CSV',
                        className: 'dt-button-custom'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'dt-button-custom',
                        title: 'Lista de Veterinarios',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        action: function () {
                            if (rutaReporte) {
                                window.open(rutaReporte, '_blank');
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'dt-button-custom',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }
                ]
            }
        }
    });
});
