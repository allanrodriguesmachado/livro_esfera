import Swal from "sweetalert2";
import 'sweetalert2/dist/sweetalert2.min.css';
export function showToast(typeError, messageError)
{
    const Toast = Swal.mixin({
        toast: true,
        position: "bottom-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    Toast.fire({
        icon: typeError,
        title: messageError
    });
}