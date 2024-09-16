document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');

    // Maneja el evento de envío del formulario
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // Evita que el formulario se envíe de manera tradicional

        // Obtiene los datos del formulario
        const formData = new FormData(loginForm);
        const email = formData.get('email');
        const password = formData.get('password');

        try {
            // Envía los datos al backend usando fetch
            const response = await fetch('../../controller/loginAction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    email: email,
                    password: password
                })
            });

            const result = await response.json();

            // Muestra los mensajes en la página
            if (result.success) {
                window.location.href = result.redirect;
            } else {
                showMessage(result.message, 'error');
            }

        } catch (error) {
            console.error('Error en la solicitud: ', error);
            showMessage('Error en la solicitud: ' + error, 'error');
        }
    });
});
