import DataTable from 'datatables.net-dt';
import $ from 'jquery';

let table = new DataTable('#myTable', {
    scrollX: true,
    paging: true,
    ordering: true,
    info: true,
    order: [[1, "asc"]],
    columns: [
        {
            data: 'livro_id',
            title: 'ID',
            visible: false
        },
        {data: 'titulo', title: 'Título'},
        {
            data: 'valor',
            title: 'Valor',
            render: function (data, type, row) {
                if (type === 'display') {
                    return new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                    }).format(data);
                }
                return data;
            }
    },
    ]
});

$('#myTable tbody').on('click', 'tr', function() {
    const data = table.row(this).data();
    if (data) {
        console.log('ID do livro clicado:', data.livro_id
        );
        console.log('Dados completos:', data);

        // Remove seleção anterior
        table.$('tr.selected').removeClass('selected');
        // Adiciona seleção na linha atual
        $(this).addClass('selected');
    }
});



export function fetch(url)
{
    mostrarLoader();

    $.ajax({
        url: url,
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            esconderLoader();

            if (response.status === 'success') {
                const livros = response.data;
                const $container = $('#livros-container');

                $container.empty();
                table.clear().rows.add(livros).draw();
            } else {
                $('#erro').removeClass('d-none').text(response.message);
            }
        },
        error: function () {
            esconderLoader();
            $('#erro').removeClass('d-none').text('Erro ao carregar os dados. Tente novamente mais tarde.');
        }
    });
}

function mostrarLoader()
{
    $('#loaderOverlay').fadeIn();
}

function esconderLoader()
{
    $('#loaderOverlay').fadeOut();
}

$('#btnRegister').on('click', () => {
    $("#livro-hide").toggleClass('d-none');
    // $("#livro-cadastro").toggleClass('d-none');
});


