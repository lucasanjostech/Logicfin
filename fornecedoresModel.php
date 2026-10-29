<?php

include_once "conexao.php";

class FornecedoresModel
{
    private $id;
    private $empresa_id;
    private $nome_razao_social;
    private $nome_fantasia;
    private $tipo_pessoa;
    private $cpf_cnpj;
    private $email;
    private $telefone;
    private $observacao;
    private $ativo;
    private $deletado;
    private $criado_em;
    private $atualizado_em;

    public function fornecedoresPorId($id)
    {
        try {
            $conexao = new ConexaoClass();
            $conn = $conexao->getConnection();

            $sql = "SELECT *
                    FROM tb_fornecedores
                    WHERE id = :id AND deletado = 'N'";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fornecedor ?: null;

        } catch (PDOException $e) {
            error_log("Erro ao buscar fornecedor por ID: " . $e->getMessage());
            return false;
        }
    }

    public function getAllFornecedores()
    {
        try {
            $conexao = new ConexaoClass();
            $conn = $conexao->getConnection();

            $sql = "SELECT *
                    FROM tb_fornecedores
                    WHERE deletado = 'N'";

            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Erro ao buscar todos os fornecedores: " . $e->getMessage());
            return false;
        }
    }

    public function createFornecedor(
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
    ) {
        try {
            $conexao = new ConexaoClass();
            $conn = $conexao->getConnection();

            $sql = "INSERT INTO tb_fornecedores (
                empresa_id,
                nome_razao_social,
                nome_fantasia,
                tipo_pessoa,
                cpf_cnpj,
                email,
                telefone,
                observacao,
                ativo,
                deletado
            ) VALUES (
                :empresa_id,
                :nome_razao_social,
                :nome_fantasia,
                :tipo_pessoa,
                :cpf_cnpj,
                :email,
                :telefone,
                :observacao,
                :ativo,
                :deletado
            )";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':empresa_id', $empresa_id);
            $stmt->bindParam(':nome_razao_social', $nome_razao_social);
            $stmt->bindParam(':nome_fantasia', $nome_fantasia);
            $stmt->bindParam(':tipo_pessoa', $tipo_pessoa);
            $stmt->bindParam(':cpf_cnpj', $cpf_cnpj);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':observacao', $observacao);
            $stmt->bindParam(':ativo', $ativo);
            $stmt->bindParam(':deletado', $deletado);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Erro ao criar fornecedor: " . $e->getMessage());
            return false;
        }
    }

    public function updateFornecedor(
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
    ) {
        try {
            $conexao = new ConexaoClass();
            $conn = $conexao->getConnection();

            $sql = "UPDATE tb_fornecedores SET
                empresa_id = :empresa_id,
                nome_razao_social = :nome_razao_social,
                nome_fantasia = :nome_fantasia,
                tipo_pessoa = :tipo_pessoa,
                cpf_cnpj = :cpf_cnpj,
                email = :email,
                telefone = :telefone,
                observacao = :observacao,
                ativo = :ativo,
                deletado = :deletado
            WHERE id = :id";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':empresa_id', $empresa_id);
            $stmt->bindParam(':nome_razao_social', $nome_razao_social);
            $stmt->bindParam(':nome_fantasia', $nome_fantasia);
            $stmt->bindParam(':tipo_pessoa', $tipo_pessoa);
            $stmt->bindParam(':cpf_cnpj', $cpf_cnpj);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':observacao', $observacao);
            $stmt->bindParam(':ativo', $ativo);
            $stmt->bindParam(':deletado', $deletado);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Erro ao atualizar fornecedor: " . $e->getMessage());
            return false;
        }
    }

    public function deleteFornecedor($id)
    {
        try {
            $conexao = new ConexaoClass();
            $conn = $conexao->getConnection();

            $sql = "UPDATE tb_fornecedores
                    SET deletado = 'S'
                    WHERE id = :id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Erro ao deletar fornecedor: " . $e->getMessage());
            return false;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getEmpresa_id()
    {
        return $this->empresa_id;
    }

    public function setEmpresa_id($empresa_id)
    {
        $this->empresa_id = $empresa_id;
        return $this;
    }

    public function getNome_razao_social()
    {
        return $this->nome_razao_social;
    }

    public function setNome_razao_social($nome_razao_social)
    {
        $this->nome_razao_social = $nome_razao_social;
        return $this;
    }

    public function getNome_fantasia()
    {
        return $this->nome_fantasia;
    }

    public function setNome_fantasia($nome_fantasia)
    {
        $this->nome_fantasia = $nome_fantasia;
        return $this;
    }

    public function getTipo_pessoa()
    {
        return $this->tipo_pessoa;
    }

    public function setTipo_pessoa($tipo_pessoa)
    {
        $this->tipo_pessoa = $tipo_pessoa;
        return $this;
    }

    public function getCpf_cnpj()
    {
        return $this->cpf_cnpj;
    }

    public function setCpf_cnpj($cpf_cnpj)
    {
        $this->cpf_cnpj = $cpf_cnpj;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getTelefone()
    {
        return $this->telefone;
    }

    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
        return $this;
    }

    public function getObservacao()
    {
        return $this->observacao;
    }

    public function setObservacao($observacao)
    {
        $this->observacao = $observacao;
        return $this;
    }

    public function getAtivo()
    {
        return $this->ativo;
    }

    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;
        return $this;
    }

    public function getDeletado()
    {
        return $this->deletado;
    }

    public function setDeletado($deletado)
    {
        $this->deletado = $deletado;
        return $this;
    }

    public function getCriado_em()
    {
        return $this->criado_em;
    }

    public function setCriado_em($criado_em)
    {
        $this->criado_em = $criado_em;
        return $this;
    }

   public function getAtualizado_em()
{
    return $this->atualizado_em;
}

/**
 * Set the value of atualizado_em
 */
public function setAtualizado_em($atualizado_em)
{
    $this->atualizado_em = $atualizado_em;

    return $this;
}
}
?>
