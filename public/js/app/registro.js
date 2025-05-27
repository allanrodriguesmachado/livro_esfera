import { showToast } from './toast';

export function registro()
{
    $('#btnSalvar').on('click', () => {
        let autor = $('#autorLivro').val();
        if (! autor) {
            showToast('error', 'O campo autor é obrigatório');
        }


    })
}