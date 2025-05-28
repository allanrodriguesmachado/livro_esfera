import DataTable from 'datatables.net-dt';
import $ from 'jquery';
import Swal from "sweetalert2";
import {showToast} from "./toast";
import {listarAssuntos, listarAutores} from "./registro";

let table = new DataTable('#myTable', {
    scrollX: true,
    paging: true,
    ordering: true,
    info: true,
    order: [[1, "asc"]],
    columnDefs: [
        {targets: '_all', className: 'text-center'}
    ],
    language: {
        decimal: ",",
        thousands: ".",
        processing: "Processando...",
        search: "Pesquisar:",
        lengthMenu: "Mostrar _MENU_ registros",
        info: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 até 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros no total)",
        loadingRecords: "Carregando...",
        zeroRecords: "Nenhum registro encontrado",
        emptyTable: "Nenhum dado disponível na tabela",
        paginate: {
            first: "Primeiro",
            previous: "Anterior",
            next: "Próximo",
            last: "Último"
        },
        aria: {
            sortAscending: ": ativar para ordenar a coluna em ordem crescente",
            sortDescending: ": ativar para ordenar a coluna em ordem decrescente"
        }
    },
    columns: [
        {
            data: 'livro_id',
            title: 'ID',
            visible: false
        },
        {data: 'titulo', title: 'Título'},
        {data: 'autor', title: 'Autor'},
        {data: 'editora', title: 'Editora'},
        {data: 'assunto', title: 'Assunto'},
        {data: 'ano_publicacao', title: 'Publicado'},
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

export function fetch(url, callback) {
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

                const livrosComAcoes = livros.map(livro => ({
                    ...livro,
                    autor_id: livro.autor_id,
                    assunto_id: livro.assunto_id,
                    acao: `
                        <button class="btn btn-sm btn-primary btn-editar" data-id="${livro.livro_id}" title="Editar">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-excluir" data-id="${livro.livro_id}" title="Excluir">
                            <i class="fa fa-trash"></i>
                        </button>
                    `
                }));

                table.clear().rows.add(livrosComAcoes).draw();

                if (typeof callback === 'function') callback();
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

$('#btnRegister').on('click', () => {
    const $cadastro = $('#livro-cadastro');
    const $lista = $('#livro-hide');

    const isCadastroVisivel = !$cadastro.hasClass('d-none');

    $cadastro.toggleClass('d-none');
    $lista.toggleClass('d-none');

    $('#btnRelatorio').toggleClass('d-none');

    const htmlBtn = isCadastroVisivel
        ? '<i class="fa fa-plus"></i> Novo Livro'
        : '<i class="fa fa-arrow-left"></i> Voltar';

    $('#btnRegister').html(htmlBtn);

    isCadastroVisivel
        ? fetch('/application/fetch')
        : (
            $('#formLivro')[0].reset(),
            $('#autorLivro').val('').removeClass('is-invalid'),
            $('#assuntoLivro').val('').removeClass('is-invalid'),
            $('#tituloLivro, #editoraLivro, #anoPublicacao, #valorLivro').removeClass('is-invalid'),
            $('.invalid-feedback').hide(),
            carregarDadosSelects(() => {
                $('#tituloLivro').focus();
            })
        );
});


function carregarDadosSelects(callback) {
    let carregouAutores = false;
    let carregouAssuntos = false;

    const tentarChamarCallback = () => {
        if (carregouAutores && carregouAssuntos && typeof callback === 'function') {
            callback();
        }
    };

    listarAutores('/autor/autores', () => {
        carregouAutores = true;
        tentarChamarCallback();
    });

    listarAssuntos('/assunto/assuntos', () => {
        carregouAssuntos = true;
        tentarChamarCallback();
    });
}

$('#myTable').on('click', '.btn-editar', function () {
    const rowData = table.row($(this).closest('tr')).data();

    Swal.fire({
        title: 'Editar Livro',
        html: `
            <input id="swal-input-titulo" class="swal2-input" placeholder="Título" value="${rowData.titulo}">
            <input id="swal-input-editora" class="swal2-input" placeholder="Editora" value="${rowData.editora || ''}">
            <input id="swal-input-ano" class="swal2-input" placeholder="Ano Publicação" type="number" value="${rowData.ano_publicacao || ''}">
            <input id="swal-input-valor" class="swal2-input" placeholder="Valor" type="text" value="${parseFloat(rowData.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}">
        `,
        didOpen: () => {
            // Limitar ano para 4 dígitos
            $('#swal-input-ano').on('input', function () {
                this.value = this.value.slice(0, 4);
            });

            // Formatar valor em tempo real
            $('#swal-input-valor').on('input', function () {
                let raw = this.value.replace(/\D/g, '');
                let float = parseFloat(raw) / 100;

                if (!isNaN(float)) {
                    this.value = float.toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    });
                } else {
                    this.value = '';
                }
            });
        },
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Salvar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const titulo = $('#swal-input-titulo').val().trim();
            const editora = $('#swal-input-editora').val().trim();
            const ano_publicacao = $('#swal-input-ano').val().trim();
            const valorFormatado = $('#swal-input-valor').val().trim();

            if (!titulo || !editora || !ano_publicacao || !valorFormatado) {
                Swal.showValidationMessage('Todos os campos são obrigatórios.');
                return false;
            }

            const valor = parseFloat(valorFormatado.replace(/[R$\s.]/g, '').replace(',', '.'));

            if (isNaN(valor) || valor <= 0) {
                Swal.showValidationMessage('Informe um valor numérico válido.');
                return false;
            }

            return {
                titulo,
                editora,
                ano_publicacao,
                valor
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const params = result.value;

            if (params.titulo === rowData.titulo) delete params.titulo;
            if (params.editora === rowData.editora) delete params.editora;
            if (params.ano_publicacao == rowData.ano_publicacao) delete params.ano_publicacao;
            if (parseFloat(params.valor) === parseFloat(rowData.valor)) delete params.valor;

            if (Object.keys(params).length === 0) {
                showToast('warning', 'Nenhuma alteração foi feita.');
                return;
            }

            atualizarLivro(rowData.livro_id, params);
        }
    });
});

const atualizarLivro = (id, params) => {
    mostrarLoader();

    $.ajax({
        url: '/application/update',
        method: 'POST',
        dataType: 'json',
        data: {
            id: id,
            params: params
        },
        success: function (response) {
            if (response.status === 'success') {
                fetch('/application/fetch', () => {
                    esconderLoader();

                    const nomesCampos = {
                        titulo: 'Título',
                        editora: 'Editora',
                        ano_publicacao: 'Ano de Publicação',
                        valor: 'Valor'
                    };

                    const alterados = Object.keys(params)
                        .filter(k => k in nomesCampos)
                        .map(k => nomesCampos[k]);

                    const mensagem = alterados.length > 0
                        ? `${alterados.join(' e ')} atualizado${alterados.length > 1 ? 's' : ''} com sucesso!`
                        : 'Livro atualizado com sucesso!';

                    showToast('success', mensagem);
                });
            } else {
                esconderLoader();
                showToast('error', response.message || 'Erro ao atualizar o livro.');
            }
        },
        error: function () {
            esconderLoader();
            $('#erro').removeClass('d-none').text('Erro ao atualizar. Tente novamente mais tarde.');
        }
    });
};


$('#myTable').on('click', '.btn-excluir', function () {
    const rowData = table.row($(this).closest('tr')).data();
    console.log('Excluir:', rowData.livro_id);

    Swal.fire({
        title: 'Deseja realmente excluir?',
        text: `Livro: "${rowData.titulo}"`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            excluirLivro(rowData.livro_id);
        }
    });
});

const excluirLivro = (id) => {
    mostrarLoader();

    $.ajax({
        url: '/application/delete',
        method: 'POST',
        dataType: 'json',
        data: {id},
        success: function (response) {
            if (response.status === 'success') {
                fetch('/application/fetch', () => {
                    showToast('success', 'Livro excluído com sucesso!');
                });
            } else {
                esconderLoader();
                showToast('error', response.message || 'Erro ao excluir o livro.');
            }
        },
        error: function () {
            esconderLoader();
            $('#erro').removeClass('d-none').text('Erro ao excluir. Tente novamente mais tarde.');
        }
    });
};

function mostrarLoader() {
    $('#loaderOverlay').fadeIn();
}

function esconderLoader() {
    $('#loaderOverlay').fadeOut();
}

$('#btnRelatorio').on('click', function () {
    mostrarLoader();

    $.ajax({
        url: '/relatorio/livros',
        method: 'POST',
        xhrFields: {
            responseType: 'blob'
        },
        success: function (response, status, xhr) {
            esconderLoader();

            const blob = new Blob([response], {type: 'application/pdf'});
            const url = window.URL.createObjectURL(blob);

            window.open(url, '_blank');
        },
        error: function () {
            esconderLoader();
            showToast('error', 'Erro ao gerar o relatório.');
        }
    });
});


