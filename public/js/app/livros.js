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
        { data: 'titulo', title: 'Título' },
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
            },
        },
        {
            data: 'acao',
            title: 'Ação',
            orderable: false,
            searchable: false
        }
    ]
});

export function fetch(url) {
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

                // Adiciona botões de ação com ícones FA
                const livrosComAcoes = livros.map(livro => ({
                    ...livro,
                    acao: `
                        <button class="btn btn-sm btn-primary btn-editar" data-id="${livro.id}" title="Editar">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-excluir" data-id="${livro.id}" title="Excluir">
                            <i class="fa fa-trash"></i>
                        </button>
                    `
                }));

                table.clear().rows.add(livrosComAcoes).draw();
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

function mostrarLoader() {
    $('#loaderOverlay').fadeIn();
}

function esconderLoader() {
    $('#loaderOverlay').fadeOut();
}

// Toggle de formulário de cadastro
$('#btnRegister').on('click', () => {
    $("#livro-hide").toggleClass('d-none');
    $("#livro-cadastro").toggleClass('d-none');
});

// Evento: excluir livro
$('#myTable').on('click', '.btn-excluir', function () {
    const livroId = $(this).data('livro_id');

    console.log(livroId)
});

$('#myTable').on('click', '.btn-editar', function () {
    const livroId = $(this).data('livro_id');

    console.log(livroId)
});
