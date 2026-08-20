document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registroFormulario");
    const ciInput = document.getElementById("CI");
    const numInput = document.getElementById("Num");
    const nombreInput = document.getElementById("Nombre");
    const apellidoInput = document.getElementById("Apellido");
    const errorCampo = document.getElementById("errorCampo");
    const errorApellido = document.getElementById("errorApellido");
    const contrasenaInput = document.getElementById("password");
    const repetirContrasenaInput = document.getElementById("password2");
    const errorContrasena = document.getElementById("errorContrasena");
    const errorFecha = document.getElementById("errorFecha");
    const fechaNacimientoInput = document.getElementById("fechaNacimiento");

    if (!form) {
        return;
    }

    if (ciInput) {
        ciInput.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 8);
        });
    }

    if (numInput) {
        numInput.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 9);
        });
    }

    form.addEventListener("submit", function (e) {
        const apellido = apellidoInput?.value.trim() || "";
        const nombre = nombreInput?.value.trim() || "";
        const ci = ciInput?.value.trim() || "";
        const email = document.getElementById("email")?.value || "";
        const contrasena = contrasenaInput?.value || "";
        const repetirContrasena = repetirContrasenaInput?.value || "";
        const fechaNacimiento = fechaNacimientoInput?.value || "";
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        let hasError = false;

        if (!nombre) {
            e.preventDefault();
            if (errorCampo) {
                errorCampo.textContent = "Este campo es obligatorio.";
            }
            if (errorApellido) {
                errorApellido.textContent = "";
            }
            hasError = true;
        } else if (errorCampo) {
            errorCampo.textContent = "";
        }

        if (!apellido) {
            e.preventDefault();
            if (errorApellido) {
                errorApellido.textContent = "Este campo es obligatorio.";
            }
            hasError = true;
        } else if (errorApellido) {
            errorApellido.textContent = "";
        }


        if (!/^\d{8}$/.test(ci)) {
            e.preventDefault();
            alert("La cédula debe tener exactamente 8 números.");
            hasError = true;
        }

        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert("Email inválido");
            hasError = true;
        }

        if (!contrasena || !repetirContrasena) {
            e.preventDefault();
            if (errorContrasena) {
                errorContrasena.textContent = "Debes completar ambos campos";
            }
            hasError = true;
        }

        if (contrasena.length < 8) {
            e.preventDefault();
            if (errorContrasena) {
                errorContrasena.textContent = "La contraseña debe tener al menos 8 caracteres";
            }
            hasError = true;
        }

        if (contrasena !== repetirContrasena) {
            e.preventDefault();
            if (errorContrasena) {
                errorContrasena.textContent = "Las contraseñas no coinciden";
            }
            hasError = true;
        }

        if (!fechaNacimiento) {
            e.preventDefault();
            if (errorFecha) {
                errorFecha.textContent = "Debes ingresar una fecha.";
            }
            hasError = true;
        }

        const fecha = new Date(fechaNacimiento);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        fecha.setHours(0, 0, 0, 0);

        if (Number.isNaN(fecha.getTime())) {
            e.preventDefault();
            if (errorFecha) {
                errorFecha.textContent = "La fecha ingresada no es válida.";
            }
            return;
        }

        if (fecha > hoy) {
            e.preventDefault();
            if (errorFecha) {
                errorFecha.textContent = "La fecha no puede ser futura.";
            }
            return;
        }

        if (fecha.getFullYear() < 1900) {
            e.preventDefault();
            if (errorFecha) {
                errorFecha.textContent = "La fecha ingresada no es válida.";
            }
            return;
        }

        if (errorFecha) {
            errorFecha.textContent = "";
        }

        if (errorContrasena) {
            errorContrasena.textContent = "";
        }

        alert("Registro correcto");
    });
});