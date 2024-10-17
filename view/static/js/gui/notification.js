const notificationDuration = 5000; // Duración predeterminada de la notificación en milisegundos
let activeNotifications = 0; // Contador de notificaciones activas

// Inicializa el toast y sus eventos
function initializeToast(toast) {
    const closeIcon = toast.querySelector(".close");
    const progress = toast.querySelector(".progress");
    let timer1, timer2;

    // Activa el toast y la barra de progreso
    toast.classList.add("active");
    progress.classList.add("active");

    // Temporizador para cerrar automáticamente el toast
    timer1 = setTimeout(() => {
        toast.classList.remove("active");
    }, notificationDuration);

    // Temporizador para detener la barra de progreso
    timer2 = setTimeout(() => {
        progress.classList.remove("active");
    }, (notificationDuration + 300));

    // Elimina el toast después de que la animación haya terminado
    setTimeout(() => {
        removeToast(toast);
    }, (notificationDuration + 600));

    // Cierra manualmente el toast al hacer clic en el icono de cerrar
    closeIcon.addEventListener("click", () => {
        toast.classList.remove("active");

        setTimeout(() => {
            progress.classList.remove("active");
        }, 300);

        clearTimeout(timer1); // Cancela el temporizador de cierre automático
        clearTimeout(timer2); // Cancela el temporizador de la barra de progreso

        setTimeout(() => {
            removeToast(toast);
        }, 300);
    });
}

// Elimina el toast y su contenedor
function removeToast(toast) {
    toast.remove(); // Elimina solo este toast
    activeNotifications--;

    // Si no quedan notificaciones, eliminar el contenedor
    if (activeNotifications === 0) {
        const notificationContainer = document.getElementById('notification-container');
        if (notificationContainer) {
            notificationContainer.remove();
        }
    }
}

// Muestra un mensaje en forma de toast
export function mostrarMensaje(mensaje, tipo = 'info', titulo = null) {
    const tipos = {
        success: { icono: 'la-check', titulo: titulo || 'Éxito' },
        error: { icono: 'la-times', titulo: titulo || 'Error' },
        info: { icono: 'la-info-circle', titulo: titulo || 'Información' },
        warning: { icono: 'la-exclamation-triangle', titulo: titulo || 'Advertencia' },
    };

    const { icono, titulo: mensajeTitulo } = tipos[tipo] || tipos.info; // Usa 'info' como predeterminado

    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        document.body.appendChild(notificationContainer);
    }

    const mensajes = mensaje.split('<br>');
    mensajes.forEach((mensaje, index) => {
        setTimeout(() => {
            const toastId = `toast_${Date.now()}_${index}`; // Añade el índice al ID
            const notificationHTML = `
                <div id="${toastId}" class="toast">
                    <div class="toast-content">
                        <i class="fas fa-solid las ${icono} check ${tipo}"></i>
                        <div class="message">
                            <span class="text text-1">${mensajeTitulo}</span>
                            <span class="text text-2">${mensaje}</span>
                        </div>
                    </div>
                    <i class="fa-solid las la-times close"></i>
                    <div class="progress ${tipo}"></div>
                </div>
            `;
            notificationContainer.insertAdjacentHTML('beforeend', notificationHTML);
            const newToast = document.getElementById(toastId);
            initializeToast(newToast);
            activeNotifications++; 
        }, index * 200); // Añade un retraso de 100 ms entre cada notificación
    });
    
}