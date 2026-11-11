<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../model/pagamentosModel.php";

// Verificar usuário e empresa da sessão
if (
    !isset($_SESSION['usuario']['id']) ||
    !isset($_SESSION['usuario']['empresa_id'])
) {
    sendJson(['error' => 'Sessão inválida ou expirada'], 401);
    exit;
}

// Roteamento das ações
if (isset($_POST['action'])) {

    if ($_POST['action'] === 'findId') {
        findById($_POST['id'] ?? null);
        exit;

    } else if ($_POST['action'] === 'findAll') {
        findAll();
        exit;

    } else if ($_POST['action'] === 'create') {
        createPagamento();
        exit;

    } else if ($_POST['action'] === 'update') {
        updatePagamento();
        exit;

    } else if (
        $_POST['action'] === 'estornar' ||
        $_POST['action'] === 'delete'
    ) {
        estornarPagamento();
        exit;

    } else {
        sendJson(['error' => 'Ação inválida'], 400);
        exit;
    }

} else {
    sendJson(['error' => 'Nenhuma ação encontrada'], 400);
    exit;
}

// Buscar pagamento por ID
function findById($id): void
{
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $empresa_id = (int) $_SESSION['usuario']['empresa_id'];

    if ($id === false || $id < 1) {
        sendJson(['error' => 'Envie um ID de pagamento válido'], 400);
        return;
    }

    try {
        $pagamentosClass = new PagamentosModel();

        $pagamento = $pagamentosClass->pagamentosPorId(
            $id,
            $empresa_id
        );

        if ($pagamento === false) {
            sendJson(['error' => 'Não foi possível buscar o pagamento'], 500);
            return;
        }

        if ($pagamento === null) {
            sendJson(['error' => 'Pagamento não encontrado'], 404);
            return;
        }

        sendJson($pagamento);

    } catch (Throwable $e) {
        error_log("Erro no findById de pagamentos: " . $e->getMessage());

        sendJson(['error' => 'Erro interno ao buscar pagamento'], 500);
    }
}

// Listar pagamentos da empresa
function findAll(): void
{
    $empresa_id = (int) $_SESSION['usuario']['empresa_id'];

    try {
        $pagamentosClass = new PagamentosModel();

        $pagamentos = $pagamentosClass->getAllPagamentos(
            $empresa_id
        );

        if ($pagamentos === false) {
            sendJson(['error' => 'Não foi possível buscar os pagamentos'], 500);
            return;
        }

        sendJson($pagamentos);

    } catch (Throwable $e) {
        error_log("Erro no findAll de pagamentos: " . $e->getMessage());

        sendJson(['error' => 'Erro interno ao listar pagamentos'], 500);
    }
}

// Cadastrar pagamento
function createPagamento(): void
{
    $parcela_id = filter_var(
        $_POST['parcela_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $contaBancariaInformada = trim(
        (string) ($_POST['conta_bancaria_id'] ?? '')
    );

    $conta_bancaria_id = null;

    if ($contaBancariaInformada !== '') {
        $conta_bancaria_id = filter_var(
            $contaBancariaInformada,
            FILTER_VALIDATE_INT
        );
    }

    $registrado_por = filter_var(
        $_SESSION['usuario']['id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $empresa_id = (int) $_SESSION['usuario']['empresa_id'];

    $data_pagamento = trim(
        (string) ($_POST['data_pagamento'] ?? '')
    );

    $valor_principal = $_POST['valor_principal'] ?? null;
    $valor_juros = $_POST['valor_juros'] ?? 0;
    $valor_multa = $_POST['valor_multa'] ?? 0;
    $valor_desconto = $_POST['valor_desconto'] ?? 0;

    $forma_pagamento = trim(
        (string) ($_POST['forma_pagamento'] ?? '')
    );

    $numero_transacao = trim(
        (string) ($_POST['numero_transacao'] ?? '')
    );

    $observacao = trim(
        (string) ($_POST['observacao'] ?? '')
    );

    // Validar data
    $dataValida = DateTime::createFromFormat(
        '!Y-m-d',
        $data_pagamento
    );

    $dataCorreta = $dataValida &&
        $dataValida->format('Y-m-d') === $data_pagamento;

    // Validar informações obrigatórias
    if (
        $parcela_id === false || $parcela_id < 1 ||
        ($conta_bancaria_id !== null &&
            ($conta_bancaria_id === false || $conta_bancaria_id < 1)) ||
        $registrado_por === false || $registrado_por < 1 ||
        !$dataCorreta ||
        $valor_principal === null || !is_numeric($valor_principal) ||
        !is_numeric($valor_juros) ||
        !is_numeric($valor_multa) ||
        !is_numeric($valor_desconto) ||
        $forma_pagamento === ''
    ) {
        sendJson(['error' => 'Informações incompletas ou inválidas'], 400);
        return;
    }

    try {
        $pagamentosClass = new PagamentosModel();

        $pagamentoCriado = $pagamentosClass->createPagamento(
            $parcela_id,
            $conta_bancaria_id,
            $registrado_por,
            $data_pagamento,
            $valor_principal,
            $valor_juros,
            $valor_multa,
            $valor_desconto,
            $forma_pagamento,
            $numero_transacao !== '' ? $numero_transacao : null,
            $observacao !== '' ? $observacao : null,
            $empresa_id
        );

        if (!$pagamentoCriado) {
            sendJson(['error' => 'Não foi possível cadastrar o pagamento'], 500);
            return;
        }

        sendJson(
            ['message' => 'Pagamento cadastrado com sucesso'],
            201
        );

    } catch (InvalidArgumentException $e) {
        sendJson(['error' => $e->getMessage()], 400);

    } catch (RuntimeException $e) {
        sendJson(['error' => $e->getMessage()], 422);

    } catch (Throwable $e) {
        error_log("Erro ao cadastrar pagamento: " . $e->getMessage());

        sendJson(['error' => 'Erro interno ao cadastrar pagamento'], 500);
    }
}

// Atualização direta não permitida
function updatePagamento(): void
{
    sendJson([
        'error' => 'Pagamentos registrados não podem ser alterados diretamente. Utilize o estorno.'
    ], 422);
}

// Estornar pagamento
function estornarPagamento(): void
{
    $id = filter_var(
        $_POST['id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $empresa_id = (int) $_SESSION['usuario']['empresa_id'];

    if ($id === false || $id < 1) {
        sendJson(['error' => 'Envie um ID de pagamento válido'], 400);
        return;
    }

    try {
        $pagamentosClass = new PagamentosModel();

        $pagamentoEstornado = $pagamentosClass->estornarPagamento(
            $id,
            $empresa_id
        );

        if (!$pagamentoEstornado) {
            sendJson(['error' => 'Não foi possível estornar o pagamento'], 500);
            return;
        }

        sendJson([
            'message' => 'Pagamento estornado com sucesso'
        ]);

    } catch (RuntimeException $e) {
        sendJson(['error' => $e->getMessage()], 422);

    } catch (Throwable $e) {
        error_log("Erro ao estornar pagamento: " . $e->getMessage());

        sendJson(['error' => 'Erro interno ao estornar pagamento'], 500);
    }
}

// Resposta JSON padronizada
function sendJson($data, int $statusCode = 200): void
{
    http_response_code($statusCode);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}