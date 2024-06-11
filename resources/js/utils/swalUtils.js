import Swal from 'sweetalert2'

export function successPopup(_title, _text){
    Swal.fire({
        title: _title,
        text: _text,
        icon: "success",
        confirmButtonText: 'OK',
        confirmButtonColor: "#088708"
    })
}

export function errorPopup(_title, _text){
    Swal.fire({
        title: _title,
        text: _text,
        icon: 'error',
        confirmButtonText: 'OK',
        confirmButtonColor: "#088708"
    })
}
