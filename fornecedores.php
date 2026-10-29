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
$globalSearchPlaceholder = 'Buscar fornecedor...';
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
    <meta name="description" content="Gestão de fornecedores do LogicFin">
    <title>Fornecedores | LogicFin</title>

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
                <span aria-current="page">Fornecedores</span>
            </nav>

            <header
                class="page-heading d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-4"
            >
                <div>
                    <div class="eyebrow mb-2">
                        <span></span>
                        Organização financeira
                    </div>
                    <h1 class="mb-2">Fornecedores</h1>
                    <p class="text-muted mb-0">
                        Consulte e organize os fornecedores da empresa.
                    </p>
                </div>

                <a class="btn btn-brand" href="fornecedor_form.php">
                    <i class="bi bi-plus-lg me-2"></i>
                    Novo fornecedor
                </a>
            </header>

            <section class="row g-3 mb-4" aria-label="Resumo dos fornecedores">
                <div class="col-sm-6 col-xl-3">
                    <article class="category-summary-card">
                        <span class="category-summary-icon summary-blue">
                            <i class="bi bi-buildings"></i>
                        </span>
                        <div>
                            <strong id="totalSuppliers">0</strong>
                            <small>Total de fornecedores</small>
                        </div>
                    </article>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <article class="category-summary-card">
                        <span class="category-summary-icon summary-green">
                            <i class="bi bi-check-circle"></i>
                        </span>
                        <div>
                            <strong id="activeSuppliers">0</strong>
                            <small>Fornecedores ativos</small>
                        </div>
                    </article>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <article class="category-summary-card">
                        <span class="category-summary-icon summary-purple">
                            <i class="bi bi-building"></i>
                        </span>
                        <div>
                            <strong id="legalEntitySuppliers">0</strong>
                            <small>Pessoas jurídicas</small>
                        </div>
                    </article>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <article class="category-summary-card">
                        <span class="category-summary-icon summary-amber">
                            <i class="bi bi-person"></i>
                        </span>
                        <div>
                            <strong id="individualSuppliers">0</strong>
                            <small>Pessoas físicas</small>
                        </div>
                    </article>
                </div>
            </section>

            <section
                class="panel-card category-list-card"
                aria-labelledby="supplierListTitle"
            >
                <div class="category-toolbar supplier-toolbar">
                    <div class="filter-search">
                        <i class="bi bi-search"></i>
                        <input
                            class="form-control"
                            id="supplierSearch"
                            type="search"
                            placeholder="Buscar por nome, documento ou contato"
                            aria-label="Buscar fornecedor"
                        >
                    </div>

                    <select
                        class="form-select"
                        id="supplierTypeFilter"
                        aria-label="Filtrar por tipo de pessoa"
                    >
                        <option value="">Todos os tipos</option>
                        <option value="J">Pessoa jurídica</option>
                        <option value="F">Pessoa física</option>
                    </select>

                    <select
                        class="form-select"
                        id="supplierStatusFilter"
                        aria-label="Filtrar por status"
                    >
                        <option value="">Todos os status</option>
                        <option value="S">Ativos</option>
                        <option value="N">Inativos</option>
                    </select>

                    <select
                        class="form-select"
                        id="supplierSort"
                        aria-label="Ordenar fornecedores"
                    >
                        <option value="name-asc">Nome: A–Z</option>
                        <option value="name-desc">Nome: Z–A</option>
                        <option value="trade-name-asc">Nome fantasia: A–Z</option>
                    </select>

                    <button
                        class="btn btn-filter-reset"
                        id="searchSupplierButton"
                        type="button"
                    >
                        <i class="bi bi-search"></i>
                        <span>Pesquisar</span>
                    </button>
                </div>

                <div class="table-responsive" id="supplierTableWrapper">
                    <table class="table category-table supplier-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th id="supplierListTitle">Fornecedor</th>
                                <th>Tipo</th>
                                <th>Documento</th>
                                <th>Contato</th>
                                <th>Status</th>
                                <th class="text-end">
                                    <span class="visually-hidden">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="supplierTableBody"></tbody>
                    </table>
                </div>

                <div class="category-empty d-none" id="supplierEmpty">
                    <span>
                        <i class="bi bi-search"></i>
                    </span>
                    <strong>Nenhum fornecedor encontrado</strong>
                    <p>Tente ajustar os filtros ou cadastre um novo fornecedor.</p>
                    <button
                        class="btn btn-outline-primary btn-sm"
                        id="emptyClearSupplierFilters"
                        type="button"
                    >
                        Limpar filtros
                    </button>
                </div>

                <div class="category-pagination">
                    <div class="page-size">
                        <span>Itens por página</span>
                        <select
                            class="form-select form-select-sm"
                            id="supplierPageSize"
                            aria-label="Itens por página"
                        >
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                        </select>
                    </div>

                    <span
                        class="pagination-summary"
                        id="supplierPaginationSummary"
                        aria-live="polite"
                    ></span>

                    <nav aria-label="Paginação dos fornecedores">
                        <ul
                            class="pagination pagination-sm mb-0"
                            id="supplierPagination"
                        ></ul>
                    </nav>
                </div>
            </section>
        </div>

        <?php require __DIR__ . '/../includes/footer.php'; ?>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div
            class="toast border-0"
            id="supplierToast"
            role="status"
            aria-live="polite"
            aria-atomic="true"
        >
            <div class="toast-body d-flex align-items-center gap-3">
                <span class="toast-check">
                    <i class="bi bi-info-lg"></i>
                </span>
                <div>
                    <strong>Ação demonstrativa</strong>
                    <div class="small text-muted">
                        Nenhum dado foi enviado ao controller.
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
    <script src="../../assets/js/fornecedores.js"></script>
</body>

</html>
