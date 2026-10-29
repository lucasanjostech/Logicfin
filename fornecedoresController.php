<?php

// declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../model/fornecedoresModel.php";

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'findId') {
        findById($_POST['id'] ?? null);
        exit;

    } else if ($_POST['action'] == 'findAll') {
        findAll();
        exit;

    } else if ($_POST['action'] == 'create') {
        createFornecedor();
        exit;

    } else if ($_POST['action'] == 'update') {
        updateFornecedor();
        exit;

    } else if ($_POST['action'] == 'delete') {
        deleteFornecedor();
        exit;

    } else {
        sendJson(['error' => 'Ação inválida'], 400);
        exit;
    }
} else {
    sendJson(['error' => 'Nenhuma ação encontrada'], 400);
    exit;
}

function findById($id): void
{
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        sendJson(['error' => 'ID inválido'], 400);
        return;
    }

    $fornecedoresClass = new FornecedoresModel();
    $fornecedor = $fornecedoresClass->fornecedoresPorId($id);

    if ($fornecedor === false) {
        sendJson(['error' => 'Erro ao buscar fornecedor'], 500);
        return;
    }

    if ($fornecedor === null) {
        sendJson(['error' => 'Fornecedor não encontrado'], 404);
        return;
    }

    sendJson($fornecedor);
}

function findAll(): void
{
    $fornecedoresClass = new FornecedoresModel();
    $fornecedores = $fornecedoresClass->getAllFornecedores();

    if ($fornecedores === false) {
        sendJson(['error' => 'Erro ao buscar fornecedores'], 500);
        return;
    }

    sendJson($fornecedores);
}

function createFornecedor(): void
{
    $empresa_id = filter_var(
        $_SESSION['usuario']['empresa_id'] ?? 0,
        FILTER_VALIDATE_INT
    );

    $nome_razao_social = trim((string) ($_POST['nome_razao_social'] ?? ''));
    $nome_fantasia = trim((string) ($_POST['nome_fantasia'] ?? ''));
    $tipo_pessoa = trim((string) ($_POST['tipo_pessoa'] ?? ''));
    $cpf_cnpj = trim((string) ($_POST['cpf_cnpj'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $observacao = trim((string) ($_POST['observacao'] ?? ''));
    $ativo = $_POST['ativo'] ?? 'S';
    $deletado = 'N';

    if (
        $empresa_id === false || $empresa_id < 1 ||
        $nome_razao_social === '' ||
        !in_array($tipo_pessoa, ['F', 'J'], true) ||
        $cpf_cnpj === ''
    ) {
        sendJson(['error' => 'Informações incompletas ou inválidas'], 400);
        return;
    }

    $fornecedoresClass = new FornecedoresModel();

    $fornecedorCriado = $fornecedoresClass->createFornecedor(
        $empresa_id,
        $nome_razao_social,
        $nome_fantasia,
        $tipo_pessoa,
        $cpf_cnpj,
        $email,
        $telefone,
        $observacao,
        $ativo,
        $deletado
    );

    if (!$fornecedorCriado) {
        sendJson(['error' => 'Não foi possível cadastrar o fornecedor'], 500);
        return;
    }

    sendJson(['message' => 'Fornecedor cadastrado com sucesso'], 201);
}

function updateFornecedor(): void
{
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    $empresa_id = filter_var(
        $_SESSION['usuario']['empresa_id'] ?? 0,
        FILTER_VALIDATE_INT
    );

    $nome_razao_social = trim((string) ($_POST['nome_razao_social'] ?? ''));
    $nome_fantasia = trim((string) ($_POST['nome_fantasia'] ?? ''));
    $tipo_pessoa = trim((string) ($_POST['tipo_pessoa'] ?? ''));
    $cpf_cnpj = trim((string) ($_POST['cpf_cnpj'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $observacao = trim((string) ($_POST['observacao'] ?? ''));
    $ativo = $_POST['ativo'] ?? 'S';
    $deletado = 'N';

    if (
        $id === false || $id < 1 ||
        $empresa_id === false || $empresa_id < 1 ||
        $nome_razao_social === '' ||
        !in_array($tipo_pessoa, ['F', 'J'], true) ||
        $cpf_cnpj === ''
    ) {
        sendJson(['error' => 'Informações incompletas ou inválidas'], 400);
        return;
    }

    $fornecedoresClass = new FornecedoresModel();
    $fornecedor = $fornecedoresClass->fornecedoresPorId($id);

    if ($fornecedor === false) {
        sendJson(['error' => 'Erro ao buscar fornecedor'], 500);
        return;
    }

    if ($fornecedor === null) {
        sendJson(['error' => 'Fornecedor não encontrado'], 404);
        return;
    }

    $fornecedorAtualizado = $fornecedoresClass->updateFornecedor(
        $id,
        $empresa_id,
        $nome_razao_social,
        $nome_fantasia,
        $tipo_pessoa,
        $cpf_cnpj,
        $email,
        $telefone,
        $observacao,
        $ativo,
        $deletado
    );

    if (!$fornecedorAtualizado) {
        sendJson(['error' => 'Não foi possível atualizar o fornecedor'], 500);
        return;
    }

    sendJson(['message' => 'Fornecedor atualizado com sucesso']);
}

function deleteFornecedor(): void
{
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        sendJson(['error' => 'ID inválido'], 400);
        return;
    }

    $fornecedoresClass = new FornecedoresModel();
    $fornecedor = $fornecedoresClass->fornecedoresPorId($id);

    if ($fornecedor === false) {
        sendJson(['error' => 'Erro ao buscar fornecedor'], 500);
        return;
    }

    if ($fornecedor === null) {
        sendJson(['error' => 'Fornecedor não encontrado'], 404);
        return;
    }

    $fornecedorDeletado = $fornecedoresClass->deleteFornecedor($id);

    if (!$fornecedorDeletado) {
        sendJson(['error' => 'Não foi possível excluir o fornecedor'], 500);
        return;
    }

    sendJson(['message' => 'Fornecedor excluído com sucesso']);
}

function sendJson($data, int $statusCode = 200): void
{
    http_response_code($statusCode);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}
