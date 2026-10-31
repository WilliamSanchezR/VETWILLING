// Envío de acciones que cambian datos (borrados) por POST con el token CSRF de la página.
// La vista imprime <input type="hidden" id="csrf_token" name="csrf_token" value="…"> fuera de
// cualquier formulario e incluye este archivo.
(function () {
  function tokenCsrf() {
    const campo = document.getElementById("csrf_token");
    return campo ? campo.value : "";
  }

  // Envía un formulario POST (navegación normal, como un enlace)
  function enviarPost(url, campos) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = url;
    form.style.display = "none";
    Object.entries({ ...campos, csrf_token: tokenCsrf() }).forEach(([nombre, valor]) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = nombre;
      input.value = valor;
      form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
  }

  // fetch POST que espera JSON
  function fetchPost(url, campos) {
    const datos = new FormData();
    Object.entries({ ...campos, csrf_token: tokenCsrf() }).forEach(([nombre, valor]) => datos.append(nombre, valor));
    return fetch(url, { method: "POST", body: datos, headers: { Accept: "application/json" } });
  }

  window.vwCsrf = { token: tokenCsrf, enviarPost, fetchPost };
})();
