<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../index.php');
    exit();
}

$nomeUsuario = htmlspecialchars(
    $_SESSION['usuario']['nome'] ?? 'Administrador',
    ENT_QUOTES,
    'UTF-8'
);

$primeiroNome = explode(' ', trim($nomeUsuario))[0] ?: 'Administrador';
$globalSearchPlaceholder = 'Buscar no LogicFin...';
$viewBasePath = '../';
$categoryBasePath = '../categoria/';
$costCenterBasePath = '../centro-custo/';
$companyBasePath = '../empresa/';
$supplierBasePath = '';
$logoutPath = '../../index.php';
?>

<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Cadastre ou edite um fornecedor no LogicFin">
    <title>Cadastro de fornecedor | LogicFin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/categories.css">
    <link rel="stylesheet" href="../../assets/css/fornecedores.css">
</head>

<body>
    <?php require __DIR__ . '/../includes/navbar.php'; ?>
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <div
        class="sidebar-backdrop"
        id="sidebarBackdrop"
        aria-hidden="true"
    ></div>

    <main class="app-main">
        <div class="container-fluid px-3 px-md-4 px-xxl-5 py-4 py-xl-5">
            <nav class="breadcrumb-nav mb-3" aria-label="Navegação estrutural">
                <a href="../index.php">Início</a>
                <i class="bi bi-chevron-right"></i>
                <a href="fornecedores.php">Fornecedores</a>
                <i class="bi bi-chevron-right"></i>
                <span id="supplierBreadcrumb" aria-current="page">Cadastro</span>
            </nav>

            <header class="page-heading d-flex align-items-start gap-3 mb-4">
                <a
                    class="back-button"
                    href="fornecedores.php"
                    aria-label="Voltar para fornecedores"
                >
                    <i class="bi bi-arrow-left"></i>
                </a>

                <div>
                    <div class="eyebrow mb-2" id="supplierEyebrow">
                        <span></span>
                        Cadastro de fornecedor
                    </div>
                    <h1 class="mb-2" id="supplierPageTitle">
                        Novo fornecedor
                    </h1>
                    <p class="text-muted mb-0" id="supplierPageDescription">
                        Informe os dados cadastrais do fornecedor.
                    </p>
                </div>
            </header>

            <form class="category-form" id="supplierForm" novalidate>
                <input type="hidden" id="supplierId" name="id" value="">

                <div class="row g-4 align-items-start">
                    <div class="col-xl-8">
                        <section
                            class="panel-card form-section-card mb-4"
                            aria-labelledby="supplierDataTitle"
                        >
                            <div class="form-section-heading">
                                <span>
                                    <i class="bi bi-card-text"></i>
                                </span>
                                <div>
                                    <h2 id="supplierDataTitle">
                                        Identificação do fornecedor
                                    </h2>
                                    <p>
                                        Informe o tipo de pessoa, o nome e o documento.
                                    </p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="supplierPersonType">
                                        Tipo de pessoa
                                        <span class="required-mark">*</span>
                                    </label>
                                    <select
                                        class="form-select"
                                        id="supplierPersonType"
                                        name="tipo_pessoa"
                                        required
                                    >
                                        <option value="J">Pessoa jurídica</option>
                                        <option value="F">Pessoa física</option>
                                    </select>
                                </div>

                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between gap-3">
                                        <label
                                            class="form-label"
                                            id="supplierNameLabel"
                                            for="supplierName"
                                        >
                                            Razão social
                                            <span class="required-mark">*</span>
                                        </label>
                                        <small class="character-counter">
                                            <span id="supplierNameCount">0</span>/180
                                        </small>
                                    </div>

                                    <input
                                        class="form-control"
                                        id="supplierName"
                                        name="nome_razao_social"
                                        type="text"
                                        maxlength="180"
                                        placeholder="Ex.: Fornecedor Exemplo Ltda."
                                        required
                                        autofocus
                                    >
                                    <div class="invalid-feedback">
                                        Informe o nome ou a razão social.
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <div class="d-flex justify-content-between gap-3">
                                        <label class="form-label" for="supplierTradeName">
                                            Nome fantasia
                                            <span class="optional-label">Opcional</span>
                                        </label>
                                        <small class="character-counter">
                                            <span id="supplierTradeNameCount">0</span>/180
                                        </small>
                                    </div>

                                    <input
                                        class="form-control"
                                        id="supplierTradeName"
                                        name="nome_fantasia"
                                        type="text"
                                        maxlength="180"
                                        placeholder="Ex.: Fornecedor Exemplo"
                                    >
                                </div>

                                <div class="col-md-5">
                                    <div class="d-flex justify-content-between gap-3">
                                        <label
                                            class="form-label"
                                            id="supplierDocumentLabel"
                                            for="supplierDocument"
                                        >
                                            CNPJ
                                            <span class="required-mark">*</span>
                                        </label>

                                        <small class="character-counter">
                                            <span id="supplierDocumentCount">0</span>
                                            /<span id="supplierDocumentLimit">14</span>
                                        </small>
                                    </div>

                                    <input
                                        class="form-control"
                                        id="supplierDocument"
                                        name="cpf_cnpj"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="14"
                                        pattern="[0-9]{14}"
                                        placeholder="00000000000000"
                                        autocomplete="off"
                                        required
                                    >

                                    <div
                                        class="invalid-feedback"
                                        id="supplierDocumentFeedback"
                                    >
                                        Informe os 14 números do CNPJ.
                                    </div>

                                    <small class="schema-hint">
                                        Digite somente números.
                                    </small>
                                </div>
                            </div>
                        </section>

                        <section
                            class="panel-card form-section-card"
                            aria-labelledby="supplierContactTitle"
                        >
                            <div class="form-section-heading">
                                <span>
                                    <i class="bi bi-person-lines-fill"></i>
                                </span>
                                <div>
                                    <h2 id="supplierContactTitle">
                                        Contato e observação
                                    </h2>
                                    <p>
                                        Estes campos são opcionais no cadastro.
                                    </p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label" for="supplierEmail">
                                        E-mail
                                        <span class="optional-label">Opcional</span>
                                    </label>
                                    <input
                                        class="form-control"
                                        id="supplierEmail"
                                        name="email"
                                        type="email"
                                        maxlength="180"
                                        placeholder="contato@fornecedor.com.br"
                                    >
                                    <div class="invalid-feedback">
                                        Informe um e-mail válido.
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label" for="supplierPhone">
                                        Telefone
                                        <span class="optional-label">Opcional</span>
                                    </label>
                                    <input
                                        class="form-control"
                                        id="supplierPhone"
                                        name="telefone"
                                        type="tel"
                                        maxlength="20"
                                        placeholder="Ex.: (92) 99999-9999"
                                    >
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="supplierNote">
                                        Observação
                                        <span class="optional-label">Opcional</span>
                                    </label>
                                    <textarea
                                        class="form-control"
                                        id="supplierNote"
                                        name="observacao"
                                        rows="4"
                                        placeholder="Informações adicionais sobre o fornecedor"
                                    ></textarea>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-xl-4">
                        <aside class="panel-card category-preview-card mb-4">
                            <div class="preview-card-heading">
                                <span>Pré-visualização</span>
                                <small>Atualização em tempo real</small>
                            </div>

                            <div class="category-preview supplier-preview">
                                <span class="supplier-preview-icon">
                                    <i class="bi bi-buildings"></i>
                                </span>
                                <div>
                                    <strong id="previewSupplierName">
                                        Nome do fornecedor
                                    </strong>
                                    <small id="previewSupplierTradeName">
                                        Sem nome fantasia
                                    </small>
                                </div>
                                <span
                                    class="status-badge category-status-active"
                                    id="previewSupplierStatus"
                                >
                                    <i></i>
                                    Ativo
                                </span>
                            </div>

                            <div class="supplier-preview-details">
                                <div>
                                    <span>Tipo</span>
                                    <strong id="previewSupplierType">
                                        Pessoa jurídica
                                    </strong>
                                </div>
                                <div>
                                    <span>Documento</span>
                                    <strong id="previewSupplierDocument">
                                        Não informado
                                    </strong>
                                </div>
                                <div>
                                    <span>Contato</span>
                                    <strong id="previewSupplierContact">
                                        Não informado
                                    </strong>
                                </div>
                            </div>
                        </aside>

                        <aside class="panel-card status-card mb-4">
                            <div>
                                <strong>Fornecedor ativo</strong>
                                <p>
                                    Desative quando ele não puder mais ser utilizado.
                                </p>
                            </div>

                            <input
                                type="hidden"
                                id="supplierActive"
                                name="ativo"
                                value="S"
                            >

                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input"
                                    id="supplierActiveToggle"
                                    type="checkbox"
                                    role="switch"
                                    aria-label="Fornecedor ativo"
                                    checked
                                >
                            </div>
                        </aside>

                        <aside class="edit-metadata d-none" id="supplierMetadata">
                            <div>
                                <span>ID do fornecedor</span>
                                <strong id="supplierIdDisplay">—</strong>
                            </div>
                        </aside>
                    </div>
                </div>

                <div class="form-actions">
                    <a class="btn btn-light" href="fornecedores.php">
                        Cancelar
                    </a>
                    <button
                        class="btn btn-brand"
                        id="saveSupplierButton"
                        type="submit"
                    >
                        <i class="bi bi-check-lg me-2"></i>
                        Salvar fornecedor
                    </button>
                </div>
            </form>
        </div>

        <?php require __DIR__ . '/../includes/footer.php'; ?>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div
            class="toast border-0"
            id="supplierFormToast"
            role="status"
            aria-live="polite"
            aria-atomic="true"
        >
            <div class="toast-body d-flex align-items-center gap-3">
                <span class="toast-check">
                    <i class="bi bi-check-lg"></i>
                </span>
                <div>
                    <strong>Formulário validado</strong>
                    <div class="small text-muted">
                        Demonstração de frontend: nenhum dado foi enviado.
                    </div>
                </div>
                <button
                    class="btn-close ms-auto"
                    type="button"
                    data-bs-dismiss="toast"
                    aria-label="Fechar"
                ></button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/fornecedor-form.js"></script>
</body>

</html>
