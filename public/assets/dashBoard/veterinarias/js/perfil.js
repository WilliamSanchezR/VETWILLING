function hideOrShowPassword(element) {
    const id = element.id;
    // Acceder al valor del elemento (si es un campo de entrada)
    const valor = element.checked;

    var nameObject = id.split('_')[1];
    var inputPass = document.getElementById(nameObject.trim());

    if (valor) {
        inputPass.type = 'text';
        document.querySelector(`#${nameObject}-visible`).style.display = "none";
        document.querySelector(`#${nameObject}-hidden`).style.display = "block";
    } else {
        inputPass.type = 'password';
        document.querySelector(`#${nameObject}-visible`).style.display = "block";
        document.querySelector(`#${nameObject}-hidden`).style.display = "none";
    }
}

document.querySelector('.avatar-icon').addEventListener('click', () => {
    document.getElementById('upload-logo').click();
});

document.getElementById('upload-logo').addEventListener('change', function() {

    document.getElementById('form_cambio_imagen').submit();
});


document.querySelector('.contrasena-actual').querySelector('.icon-view').addEventListener('click', ()=> {
    document.querySelector('#ver_contrasena-actual').click();
});

document.querySelector('.nueva-contrasena').querySelector('.icon-view').addEventListener('click', ()=> {
    document.querySelector('#ver_nueva-contrasena').click();
});


document.querySelector('.confi-contrasena').querySelector('.icon-view').addEventListener('click', ()=> {
    document.querySelector('#ver_confi-contrasena').click();
});


