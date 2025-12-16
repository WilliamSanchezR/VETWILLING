document.querySelector('.avatar-icon').addEventListener('click', () => {
    document.getElementById('upload-logo').click();
});

document.getElementById('upload-logo').addEventListener('change', function() {

    document.getElementById('form_cambio_imagen').submit();
});
