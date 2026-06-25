document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registerForm");
    const ciInput = document.getElementById("CI");

    if (!form || !ciInput) {
        return;
    }

    ciInput.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 8);
    });

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const ci = ciInput.value.trim();
        const email = document.getElementById("email")?.value || "";
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!/^\d{8}$/.test(ci)) {
            alert("La cédula debe tener exactamente 8 números.");
            return;
        }

        if (!emailRegex.test(email)) {
            alert("Email inválido");
            return;
        }

        alert("Registro correcto");
        window.location.href = "/login";
    });
});