import {showToast} from './toast';
import $ from "jquery";
import Swal from "sweetalert2";

export function registro(url) {
    $('#anoPublicacao').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    $('#valorLivro').on('input', function () {
        let value = this.value.replace(/\D/g, '');
        value = (parseInt(value, 10) / 100).toFixed(2);
        this.value = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    });

    $('#btnSalvar').on('click', (e) => {
        e.preventDefault();

        $('#tituloLivro, #editoraLivro, #anoPublicacao, #valorLivro, #autorLivro, #assuntoLivro').removeClass('is-invalid');

        let formValido = true;

        function marcarInvalido(selector) {
            $(selector).addClass('is-invalid');
            formValido = false;
        }

        const titulo = $('#tituloLivro').val().trim();
        const editora = $('#editoraLivro').val().trim();
        const anoPublicacao = $('#anoPublicacao').val().trim();
        const valor = $('#valorLivro').val().trim();
        const autorId = $('#autorLivro').val();
        const assuntoId = $('#assuntoLivro').val();

        if (!titulo) marcarInvalido('#tituloLivro');
        if (!editora) marcarInvalido('#editoraLivro');
        if (!anoPublicacao) marcarInvalido('#anoPublicacao');
        if (!valor) marcarInvalido('#valorLivro');
        if (!autorId) marcarInvalido('#autorLivro');
        if (!assuntoId) marcarInvalido('#assuntoLivro');

        if (!formValido) {
            showToast('error', 'Preencha todos os campos obrigatórios.');
            $('#tituloLivro').focus();
            return;
        }

        const formData = {
            titulo,
            editora,
            ano_publicacao: anoPublicacao,
            valor: valor.replace(/[R$\s.]/g, '').replace(',', '.'),
            autor_id: autorId,
            assunto_id: assuntoId
        };

        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: formData,
            success: function (response) {
                if (response.status === 'success') {
                    showToast('success', 'Livro salvo com sucesso!');
                    $('#formLivro')[0].reset();
                    $('#autorLivro').val('');
                    $('#assuntoLivro').val('');
                } else {
                    showToast('error', response.message || 'Erro ao salvar o livro.');
                }
            },
            error: function () {
                $('#erro').removeClass('d-none').text('Erro ao salvar. Tente novamente mais tarde.');
            }
        });
    });

}

export function listarAutores(url = '/autor/autores', callback) {
    mostrarLoader();

    $.ajax({
        url: url,
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            esconderLoader();

            if (response.status === 'success') {
                const autores = response.data;
                const $select = $('#autorLivro');

                $select.empty().append('<option value="">Selecione um autor</option>');

                autores.forEach(autor => {
                    $select.append(`<option value="${autor.id}">${autor.nome}</option>`);
                });

                if (typeof callback === 'function') callback();
            } else {
                $('#erro').removeClass('d-none').text(response.message || 'Erro ao carregar autores');
            }
        },
        error: function () {
            esconderLoader();
            $('#erro').removeClass('d-none').text('Erro ao carregar autores. Tente novamente mais tarde.');
        }
    });
}

export function listarAssuntos(url = '/assunto/assuntos', callback) {
    mostrarLoader();

    $.ajax({
        url: url,
        method: 'POST',
        dataType: 'json',
        success: function (response) {
            esconderLoader();

            if (response.status === 'success') {
                const assuntos = response.data;
                const $select = $('#assuntoLivro');

                $select.empty().append('<option value="">Selecione um assunto</option>');

                assuntos.forEach(assunto => {
                    $select.append(`<option value="${assunto.id}">${assunto.nome}</option>`);
                });

                if (typeof callback === 'function') callback();
            } else {
                $('#erro').removeClass('d-none').text(response.message || 'Erro ao carregar assuntos');
            }
        },
        error: function () {
            esconderLoader();
            $('#erro').removeClass('d-none').text('Erro ao carregar assuntos. Tente novamente mais tarde.');
        }
    });
}

function mostrarLoader() {
    $('#loaderOverlay').fadeIn();
}

function esconderLoader() {
    $('#loaderOverlay').fadeOut();
}

$('#btnSalvarAutor').on('click', () => {
    const nome = $('#novoAutor').val().trim();
    if (!nome) {
        showToast('error', 'Informe o nome do autor.');
        return;
    }

    mostrarLoader();

    $.ajax({
        url: '/autor/criar',
        method: 'POST',
        dataType: 'json',
        data: {nome: nome},
        success: function (response) {
            esconderLoader();

            if (response.status === 'success') {
                const novoId = response.data.id;
                const novoNome = response.data.nome;

                $('#autorLivro').append(`<option value="${novoId}" selected>${novoNome}</option>`);
                $('#modalAutor').modal('hide');
                $('#novoAutor').val('');
                showToast('success', 'Autor cadastrado com sucesso!');
            } else {
                showToast('error', response.message || 'Erro ao cadastrar autor.');
            }
        },
        error: function () {
            esconderLoader();
            showToast('error', 'Erro ao cadastrar autor. Tente novamente.');
        }
    });
});

$('#btnSalvarAssunto').on('click', () => {
    const nome = $('#novoAssunto').val().trim();
    if (!nome) {
        showToast('error', 'Informe o nome do assunto.');
        return;
    }

    mostrarLoader();

    $.ajax({
        url: '/assunto/criar',
        method: 'POST',
        dataType: 'json',
        data: {nome: nome},
        success: function (response) {
            esconderLoader();

            if (response.status === 'success') {
                const novoId = response.data.id;
                const novoNome = response.data.nome;

                $('#assuntoLivro').append(`<option value="${novoId}" selected>${novoNome}</option>`);
                $('#modalAssunto').modal('hide');
                $('#novoAssunto').val('');
                showToast('success', 'Assunto cadastrado com sucesso!');
            } else {
                showToast('error', response.message || 'Erro ao cadastrar assunto.');
            }
        },
        error: function () {
            esconderLoader();
            showToast('error', 'Erro ao cadastrar assunto. Tente novamente.');
        }
    });
});


function carregarListaAssuntos() {
    $.post('/assunto/assuntos', function (res) {
        if (res.status === 'success') {
            const $lista = $('#listaAssuntos').empty();
            res.data.forEach(assunto => {
                const item = `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="assunto-nome" data-id="${assunto.id}">${assunto.nome}</span>
                        <div>
                            <button class="btn btn-sm btn-outline-primary me-1 btn-editar-assunto" data-id="${assunto.id}" data-nome="${assunto.nome}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-excluir-assunto" data-id="${assunto.id}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </li>`;
                $lista.append(item);
            });
        }
    });
}

$('#modalAssunto').on('shown.bs.modal', function () {
    carregarListaAssuntos();
});

$(document).on('click', '.btn-editar-assunto', function () {
    const id = $(this).data('id');
    const nomeAtual = String($(this).data('nome') || '');

    $('#modalAssunto').modal('hide');

    setTimeout(() => {
        Swal.fire({
            title: 'Editar Assunto',
            html: `
                <input id="swal-nome-assunto" class="swal2-input" value="${nomeAtual}" placeholder="Nome do assunto" autofocus>
            `,
            showCancelButton: true,
            confirmButtonText: 'Salvar',
            cancelButtonText: 'Cancelar',
            focusConfirm: false,
            preConfirm: () => {
                const novoNome = document.getElementById('swal-nome-assunto').value.trim();
                if (!novoNome) {
                    Swal.showValidationMessage('Informe um nome válido.');
                    return false;
                }
                return novoNome;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/assunto/editar', {id, nome: result.value}, function (res) {
                    if (res.status === 'success') {
                        showToast('success', 'Assunto atualizado!');
                        carregarListaAssuntos();
                        listarAutores();
                    } else {
                        showToast('error', res.message || 'Erro ao editar.');
                    }
                });
            }
        });
    }, 300);
});

$(document).on('click', '.btn-excluir-assunto', function () {
    const id = $(this).data('id');

    $('#modalAssunto').modal('hide');

    setTimeout(() => {
        Swal.fire({
            title: 'Excluir Assunto',
            text: 'Tem certeza que deseja excluir este assunto?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/assunto/excluir', {id}, function (res) {
                    if (res.status === 'success') {
                        showToast('success', 'Assunto excluído com sucesso!');
                        carregarListaAssuntos();
                        listarAutores();
                    } else {
                        showToast('error', res.message || 'Erro ao excluir assunto.');
                    }
                }).fail(() => {
                    showToast('error', 'Erro ao excluir assunto. Tente novamente.');
                });
            } else {
                $('#modalAssunto').modal('show');
            }
        });
    }, 300);
});


function carregarListaAutores() {
    $.post('/autor/autores', function (res) {
        if (res.status === 'success') {
            const $lista = $('#listaAutores').empty();
            res.data.forEach(autor => {
                const item = `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="autor-nome" data-id="${autor.id}">${autor.nome}</span>
                        <div>
                            <button class="btn btn-sm btn-outline-primary me-1 btn-editar-autor" data-id="${autor.id}" data-nome="${autor.nome}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-excluir-autor" data-id="${autor.id}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </li>`;
                $lista.append(item);
            });
        }
    });
}

$('#modalAutor').on('shown.bs.modal', function () {
    carregarListaAutores();
});

$(document).on('click', '.btn-editar-autor', function () {
    const id = $(this).data('id');
    const nomeAtual = String($(this).data('nome') || '');

    $('#modalAutor').modal('hide');

    setTimeout(() => {
        Swal.fire({
            title: 'Editar Autor',
            html: `<input id="swal-nome-autor" class="swal2-input" value="${nomeAtual}" placeholder="Nome do autor" autofocus>`,
            showCancelButton: true,
            confirmButtonText: 'Salvar',
            cancelButtonText: 'Cancelar',
            focusConfirm: false,
            preConfirm: () => {
                const novoNome = document.getElementById('swal-nome-autor').value.trim();
                if (!novoNome) {
                    Swal.showValidationMessage('Informe um nome válido.');
                    return false;
                }
                return novoNome;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/autor/editar', {id, nome: result.value}, function (res) {
                    if (res.status === 'success') {
                        showToast('success', 'Autor atualizado!');
                        carregarListaAutores();
                        listarAutores();
                    } else {
                        showToast('error', res.message || 'Erro ao editar.');
                    }
                });
            }
        });
    }, 300);
});

$(document).on('click', '.btn-excluir-autor', function () {
    const id = $(this).data('id');

    $('#modalAutor').modal('hide');

    setTimeout(() => {
        Swal.fire({
            title: 'Excluir Autor',
            text: 'Tem certeza que deseja excluir este autor?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/autor/excluir', {id}, function (res) {
                    if (res.status === 'success') {
                        showToast('success', 'Autor excluído com sucesso!');
                        carregarListaAutores();
                        listarAutores();
                    } else {
                        showToast('error', res.message || 'Erro ao excluir autor.');
                    }
                }).fail(() => {
                    showToast('error', 'Erro ao excluir autor. Tente novamente.');
                });
            } else {
                $('#modalAutor').modal('show');
            }
        });
    }, 300);
});
