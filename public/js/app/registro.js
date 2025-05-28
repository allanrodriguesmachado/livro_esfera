import { showToast } from './toast';
import $ from "jquery";

export function registro(url)
{
    $('#btnSalvar').on('click', (e) => {
        e.preventDefault();

        const form = $('#formLivro');
        const formData = form.serialize();

        if (! $('#autorLivro').val()) {
            showToast('error', 'O campo autor é obrigatório');
            return;
        }

        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: {
                formData: formData,
            },
            success: function (response) {
                console.log(response.data);
            },
            error: function () {
                $('#erro').removeClass('d-none').text('Erro ao carregar os dados. Tente novamente mais tarde.');
            }
        });
    })
}