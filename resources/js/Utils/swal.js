import Swal from 'sweetalert2';

// Mixin padrão para Toasts de feedback rápido
export const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    background: '#ffffff',
    customClass: {
        popup: 'rounded-2xl border border-slate-100 shadow-xl'
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
        
        // Estilizar barra de progresso dinamicamente se a cor primária estiver no CSS
        const progress = toast.querySelector('.swal2-timer-progress-bar');
        if (progress) {
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
            if (primaryColor) {
                progress.style.backgroundColor = primaryColor;
            }
        }
    }
});

/**
 * Disparar toast de Sucesso.
 */
export function notifySuccess(message) {
    Toast.fire({
        icon: 'success',
        title: message,
    });
}

/**
 * Disparar modal de Erro.
 */
export function notifyError(message, title = 'Ocorreu um erro') {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#004b23';
    
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'Entendido',
        confirmButtonColor: primaryColor,
        customClass: {
            popup: 'rounded-3xl border border-slate-100 shadow-2xl',
            confirmButton: 'px-8 py-3.5 rounded-full font-bold text-xs uppercase tracking-widest'
        }
    });
}

/**
 * Confirmar ação crítica.
 */
export function confirmAction(title, text, confirmText = 'Confirmar', cancelText = 'Cancelar') {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#004b23';
    
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: primaryColor,
        cancelButtonColor: '#ef4444',
        customClass: {
            popup: 'rounded-3xl border border-slate-100 shadow-2xl',
            confirmButton: 'px-6 py-3 rounded-xl font-bold text-sm',
            cancelButton: 'px-6 py-3 rounded-xl font-bold text-sm'
        }
    });
}

// Helpers exportados para uso global
export default {
    toast: Toast,
    success: notifySuccess,
    error: notifyError,
    confirm: confirmAction
};
