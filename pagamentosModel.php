<?php

require_once __DIR__ . "/conexao.php";

class PagamentosModel
{
    private $id;
    private $parcela_id;
    private $conta_bancaria_id;
    private $registrado_por;
    private $data_pagamento;
    private $valor_principal;
    private $valor_juros;
    private $valor_multa;
    private $valor_desconto;
    private $valor_total;
    private $forma_pagamento;
    private $numero_transacao;
    private $observacao;
    private $criado_em;
    private $estornado_em;

    // Converter valores monetários para centavos sem utilizar float
    private function valorParaCentavos($valor, string $campo): int
    {
        $valor = trim((string) $valor);

        if (!preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $valor, $partes)) {
            throw new RuntimeException(
                "O valor informado para {$campo} é inválido."
            );
        }

        $inteiro = (int) $partes[1];

        if ($inteiro > intdiv(PHP_INT_MAX, 100)) {
            throw new RuntimeException(
                "O valor informado para {$campo} é muito alto."
            );
        }

        $fracao = str_pad($partes[2] ?? '0', 2, '0');

        return ($inteiro * 100) + (int) $fracao;
    }

    // Converter centavos para formato decimal aceito pelo banco
    private function centavosParaDecimal(int $centavos): string
    {
        $inteiro = intdiv($centavos, 100);
        $fracao = $centavos % 100;

        return sprintf('%d.%02d', $inteiro, $fracao);
    }

    // Buscar pagamento por ID
    public function pagamentosPorId($id, $empresa_id = null)
    {
        try {
            $conexao = new ConexaoClass();
            $conn = $conexao->getConnection();

            $sql = "SELECT p.*
                    FROM tb_pagamentos p
                    INNER JOIN tb_contas_pagar_parcelas cp
                        ON cp.id = p.parcela_id
                    INNER JOIN tb_contas_pagar c
                        ON c.id = cp.conta_pagar_id
                    WHERE p.id = :id";

            if ($empresa_id !== null) {
                $sql .= " AND c.empresa_id = :empresa_id";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            if ($empresa_id !== null) {
                $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
            }

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            error_log("Erro ao buscar pagamento: " . $e->getMessage());
            return false;
        }
    }

    // Listar pagamentos
    public function getAllPagamentos($empresa_id = null)
    {
        try {
            $conexao = new ConexaoClass();
            $conn = $conexao->getConnection();

            $sql = "SELECT
                        p.*,
                        cb.banco,
                        cb.descricao AS descricao_conta,
                        COALESCE(NULLIF(cb.descricao, ''), cb.banco) AS conta_bancaria
                    FROM tb_pagamentos p
                    LEFT JOIN tb_contas_bancarias cb
                        ON cb.id = p.conta_bancaria_id
                    INNER JOIN tb_contas_pagar_parcelas cp
                        ON cp.id = p.parcela_id
                    INNER JOIN tb_contas_pagar c
                        ON c.id = cp.conta_pagar_id";

            if ($empresa_id !== null) {
                $sql .= " WHERE c.empresa_id = :empresa_id";
            }

            $sql .= " ORDER BY p.id DESC";

            $stmt = $conn->prepare($sql);

            if ($empresa_id !== null) {
                $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
            }

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Erro ao listar pagamentos: " . $e->getMessage());
            return false;
        }
    }

    // Registrar pagamento com atualização automática dos status
    public function createPagamento(
        $parcela_id,
        $conta_bancaria_id,
        $registrado_por,
        $data_pagamento,
        $valor_principal,
        $valor_juros,
        $valor_multa,
        $valor_desconto,
        $forma_pagamento,
        $numero_transacao = null,
        $observacao = null,
        $empresa_id = null
    ) {
        if (empty($empresa_id) || $empresa_id <= 0) {
            throw new InvalidArgumentException(
                "Empresa não identificada para registrar o pagamento."
            );
        }

        $conexao = new ConexaoClass();
        $conn = $conexao->getConnection();

        try {
            $conn->beginTransaction();

            // Bloquear e validar a parcela e sua conta
            $sql = "SELECT
                        cp.id,
                        cp.conta_pagar_id,
                        cp.valor,
                        cp.status AS status_parcela,
                        c.status AS status_conta
                    FROM tb_contas_pagar_parcelas cp
                    INNER JOIN tb_contas_pagar c
                        ON c.id = cp.conta_pagar_id
                    WHERE cp.id = :parcela_id
                      AND c.empresa_id = :empresa_id
                    FOR UPDATE";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':parcela_id', $parcela_id, PDO::PARAM_INT);
            $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
            $stmt->execute();

            $parcela = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$parcela) {
                throw new RuntimeException(
                    "Parcela não encontrada para esta empresa."
                );
            }

            if (
                $parcela['status_parcela'] === 'CANCELADA' ||
                $parcela['status_conta'] === 'CANCELADA'
            ) {
                throw new RuntimeException(
                    "Não é possível pagar uma parcela ou conta cancelada."
                );
            }

            // Validar conta bancária, quando informada
            if ($conta_bancaria_id !== null && $conta_bancaria_id !== '') {
                $sql = "SELECT id
                        FROM tb_contas_bancarias
                        WHERE id = :conta_bancaria_id
                          AND empresa_id = :empresa_id
                          AND ativo = 'S'";

                $stmt = $conn->prepare($sql);
                $stmt->bindValue(
                    ':conta_bancaria_id',
                    $conta_bancaria_id,
                    PDO::PARAM_INT
                );
                $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
                $stmt->execute();

                if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                    throw new RuntimeException(
                        "Conta bancária inválida ou inativa."
                    );
                }
            } else {
                $conta_bancaria_id = null;
            }

            // Conferir valores utilizando centavos
            $principalCentavos = $this->valorParaCentavos(
                $valor_principal,
                'valor principal'
            );

            $jurosCentavos = $this->valorParaCentavos(
                $valor_juros,
                'juros'
            );

            $multaCentavos = $this->valorParaCentavos(
                $valor_multa,
                'multa'
            );

            $descontoCentavos = $this->valorParaCentavos(
                $valor_desconto,
                'desconto'
            );

            if ($principalCentavos <= 0) {
                throw new RuntimeException(
                    "O valor principal deve ser maior que zero."
                );
            }

            if (
                $descontoCentavos >
                ($principalCentavos + $jurosCentavos + $multaCentavos)
            ) {
                throw new RuntimeException(
                    "O desconto não pode ser maior que o valor do pagamento."
                );
            }

            // Calcular o principal já pago, desconsiderando estornos
            $sql = "SELECT COALESCE(SUM(valor_principal), 0)
                    FROM tb_pagamentos
                    WHERE parcela_id = :parcela_id
                      AND estornado_em IS NULL";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':parcela_id', $parcela_id, PDO::PARAM_INT);
            $stmt->execute();

            $principalPagoCentavos = $this->valorParaCentavos(
                $stmt->fetchColumn(),
                'total já pago'
            );

            $valorParcelaCentavos = $this->valorParaCentavos(
                $parcela['valor'],
                'valor da parcela'
            );

            $saldoPrincipalCentavos =
                $valorParcelaCentavos - $principalPagoCentavos;

            if ($principalCentavos > $saldoPrincipalCentavos) {
                $saldoFormatado = number_format(
                    $saldoPrincipalCentavos / 100,
                    2,
                    ',',
                    '.'
                );

                throw new RuntimeException(
                    "O pagamento ultrapassa o saldo principal da parcela. " .
                    "Saldo disponível: R$ " . $saldoFormatado
                );
            }

            // Inserir pagamento
            // valor_total é gerado automaticamente pelo MySQL
            $sql = "INSERT INTO tb_pagamentos (
                        parcela_id,
                        conta_bancaria_id,
                        registrado_por,
                        data_pagamento,
                        valor_principal,
                        valor_juros,
                        valor_multa,
                        valor_desconto,
                        forma_pagamento,
                        numero_transacao,
                        observacao
                    ) VALUES (
                        :parcela_id,
                        :conta_bancaria_id,
                        :registrado_por,
                        :data_pagamento,
                        :valor_principal,
                        :valor_juros,
                        :valor_multa,
                        :valor_desconto,
                        :forma_pagamento,
                        :numero_transacao,
                        :observacao
                    )";

            $stmt = $conn->prepare($sql);

            $stmt->bindValue(':parcela_id', $parcela_id, PDO::PARAM_INT);

            $stmt->bindValue(
                ':conta_bancaria_id',
                $conta_bancaria_id,
                $conta_bancaria_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':registrado_por',
                $registrado_por,
                $registrado_por === null ? PDO::PARAM_NULL : PDO::PARAM_INT
            );

            $stmt->bindValue(':data_pagamento', $data_pagamento);

            $stmt->bindValue(
                ':valor_principal',
                $this->centavosParaDecimal($principalCentavos)
            );

            $stmt->bindValue(
                ':valor_juros',
                $this->centavosParaDecimal($jurosCentavos)
            );

            $stmt->bindValue(
                ':valor_multa',
                $this->centavosParaDecimal($multaCentavos)
            );

            $stmt->bindValue(
                ':valor_desconto',
                $this->centavosParaDecimal($descontoCentavos)
            );

            $stmt->bindValue(':forma_pagamento', $forma_pagamento);
            $stmt->bindValue(':numero_transacao', $numero_transacao);
            $stmt->bindValue(':observacao', $observacao);

            if (!$stmt->execute()) {
                throw new RuntimeException(
                    "Não foi possível registrar o pagamento."
                );
            }

            // Atualizar status da parcela
            if (
                !$this->atualizarStatusParcela(
                    $conn,
                    $parcela_id
                )
            ) {
                throw new RuntimeException(
                    "Não foi possível atualizar o status da parcela."
                );
            }

            // Atualizar status da conta principal
            if (
                !$this->atualizarStatusConta(
                    $conn,
                    $parcela['conta_pagar_id'],
                    $empresa_id
                )
            ) {
                throw new RuntimeException(
                    "Não foi possível atualizar o status da conta."
                );
            }

            $conn->commit();

            return true;

        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            error_log("Erro ao criar pagamento: " . $e->getMessage());

            throw $e;
        }
    }

    // Atualizar status de uma parcela
    private function atualizarStatusParcela($conn, $parcela_id)
    {
        $sql = "UPDATE tb_contas_pagar_parcelas cp
                SET status = CASE
                    WHEN cp.status = 'CANCELADA' THEN 'CANCELADA'

                    WHEN (
                        SELECT COALESCE(SUM(p.valor_principal), 0)
                        FROM tb_pagamentos p
                        WHERE p.parcela_id = cp.id
                          AND p.estornado_em IS NULL
                    ) >= cp.valor THEN 'PAGA'

                    WHEN (
                        SELECT COALESCE(SUM(p.valor_principal), 0)
                        FROM tb_pagamentos p
                        WHERE p.parcela_id = cp.id
                          AND p.estornado_em IS NULL
                    ) > 0 THEN 'PARCIAL'

                    ELSE 'PENDENTE'
                END
                WHERE cp.id = :parcela_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':parcela_id', $parcela_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Atualizar status da conta principal
    private function atualizarStatusConta($conn, $conta_pagar_id, $empresa_id)
    {
        $sql = "UPDATE tb_contas_pagar c
                SET status = CASE
                    WHEN c.status = 'CANCELADA' THEN 'CANCELADA'

                    WHEN (
                        SELECT COUNT(*)
                        FROM tb_contas_pagar_parcelas cp
                        WHERE cp.conta_pagar_id = c.id
                          AND cp.status <> 'CANCELADA'
                    ) = 0 THEN 'PENDENTE'

                    WHEN (
                        SELECT COUNT(*)
                        FROM tb_contas_pagar_parcelas cp
                        WHERE cp.conta_pagar_id = c.id
                          AND cp.status <> 'CANCELADA'
                          AND cp.status = 'PAGA'
                    ) = (
                        SELECT COUNT(*)
                        FROM tb_contas_pagar_parcelas cp
                        WHERE cp.conta_pagar_id = c.id
                          AND cp.status <> 'CANCELADA'
                    ) THEN 'PAGA'

                    WHEN EXISTS (
                        SELECT 1
                        FROM tb_contas_pagar_parcelas cp
                        WHERE cp.conta_pagar_id = c.id
                          AND cp.status IN ('PARCIAL', 'PAGA')
                    ) THEN 'PARCIAL'

                    ELSE 'PENDENTE'
                END
                WHERE c.id = :conta_pagar_id
                  AND c.empresa_id = :empresa_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':conta_pagar_id', $conta_pagar_id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Estornar pagamento, preservando o histórico
    public function estornarPagamento($id, $empresa_id)
    {
        $conexao = new ConexaoClass();
        $conn = $conexao->getConnection();

        try {
            $conn->beginTransaction();

            // Localizar e bloquear pagamento e parcela
            $sql = "SELECT
                        p.id,
                        p.parcela_id,
                        cp.conta_pagar_id
                    FROM tb_pagamentos p
                    INNER JOIN tb_contas_pagar_parcelas cp
                        ON cp.id = p.parcela_id
                    INNER JOIN tb_contas_pagar c
                        ON c.id = cp.conta_pagar_id
                    WHERE p.id = :id
                      AND c.empresa_id = :empresa_id
                      AND p.estornado_em IS NULL
                    FOR UPDATE";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
            $stmt->execute();

            $pagamento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pagamento) {
                throw new RuntimeException(
                    "Pagamento não encontrado ou já estornado."
                );
            }

            // Registrar estorno
            $sql = "UPDATE tb_pagamentos
                    SET estornado_em = CURRENT_TIMESTAMP()
                    WHERE id = :id
                      AND estornado_em IS NULL";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute() || $stmt->rowCount() !== 1) {
                throw new RuntimeException(
                    "Não foi possível estornar o pagamento."
                );
            }

            // Recalcular o status da parcela
            if (
                !$this->atualizarStatusParcela(
                    $conn,
                    $pagamento['parcela_id']
                )
            ) {
                throw new RuntimeException(
                    "Não foi possível atualizar o status da parcela após o estorno."
                );
            }

            // Recalcular o status da conta
            if (
                !$this->atualizarStatusConta(
                    $conn,
                    $pagamento['conta_pagar_id'],
                    $empresa_id
                )
            ) {
                throw new RuntimeException(
                    "Não foi possível atualizar o status da conta após o estorno."
                );
            }

            $conn->commit();

            return true;

        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            error_log("Erro ao estornar pagamento: " . $e->getMessage());

            throw $e;
        }
    }

    // Atualização direta desativada para preservar a consistência financeira
    public function updatePagamento(
        $id,
        $parcela_id,
        $conta_bancaria_id,
        $registrado_por,
        $data_pagamento,
        $valor_principal,
        $valor_juros,
        $valor_multa,
        $valor_desconto,
        $forma_pagamento,
        $numero_transacao = null,
        $observacao = null
    ) {
        throw new RuntimeException(
            "Pagamentos registrados não podem ser alterados diretamente. " .
            "Utilize o procedimento de estorno."
        );
    }

    // Exclusão física desativada para preservar o histórico
    public function deletePagamento($id)
    {
        throw new RuntimeException(
            "Pagamentos não podem ser excluídos fisicamente. " .
            "Utilize o procedimento de estorno."
        );
    }
}