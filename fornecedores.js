$(function () {
    // Os fornecedores serão carregados pelo controller.
    let suppliers = [];

    const state = {
        search: '',
        personType: '',
        status: '',
        sort: 'name-asc',
        page: 1,
        pageSize: 5
    };

    const controllerUrl = '../../controller/fornecedoresController.php';

    const $sidebar = $('#appSidebar');
    const $backdrop = $('#sidebarBackdrop');
    const $sidebarToggle = $('#sidebarToggle');

    function setSidebar(open) {
        $sidebar.toggleClass('show', open);
        $backdrop.toggleClass('show', open);
        $backdrop.attr('aria-hidden', String(!open));
        $sidebarToggle.attr('aria-expanded', String(open));
        $sidebarToggle.attr('aria-label', open ? 'Fechar menu' : 'Abrir menu');
        $sidebarToggle.find('i').attr(
            'class',
            open ? 'bi bi-x-lg' : 'bi bi-list'
        );
    }

    function normalizeText(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function formatDocument(value, personType) {
        const digits = String(value || '').replace(/\D/g, '');

        if (personType === 'F' && digits.length === 11) {
            return digits.replace(
                /^(\d{3})(\d{3})(\d{3})(\d{2})$/,
                '$1.$2.$3-$4'
            );
        }

        if (personType === 'J' && digits.length === 14) {
            return digits.replace(
                /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/,
                '$1.$2.$3/$4-$5'
            );
        }

        return '';
    }

    function updateSummary() {
        $('#totalSuppliers').text(suppliers.length);

        $('#activeSuppliers').text(
            suppliers.filter(function (supplier) {
                return supplier.ativo === 'S';
            }).length
        );

        $('#legalEntitySuppliers').text(
            suppliers.filter(function (supplier) {
                return supplier.tipo_pessoa === 'J';
            }).length
        );

        $('#individualSuppliers').text(
            suppliers.filter(function (supplier) {
                return supplier.tipo_pessoa === 'F';
            }).length
        );
    }

    function loadSuppliers() {
        $.ajax({
            url: controllerUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'findAll'
            }
        })
            .done(function (response) {
                if (!Array.isArray(response)) {
                    console.error(
                        'Resposta inesperada do controller de fornecedores:',
                        response
                    );

                    suppliers = [];
                    updateSummary();
                    render();

                    showToast(
                        'Erro ao carregar fornecedores',
                        'O controller não retornou uma lista válida.'
                    );

                    return;
                }

                suppliers = response;
                state.page = 1;

                updateSummary();
                render();
            })
            .fail(function (xhr, status, error) {
                console.error(
                    'Erro ao carregar fornecedores:',
                    status,
                    error,
                    xhr.responseText
                );

                suppliers = [];
                updateSummary();
                render();

                showToast(
                    'Erro ao carregar fornecedores',
                    'Não foi possível consultar os fornecedores. Verifique o controller e a conexão com o banco de dados.'
                );
            });
    }

    function showToast(title, message) {
        const toastElement = document.getElementById('supplierToast');

        if (!toastElement) {
            return;
        }

        $(toastElement).find('.toast-body strong').text(title);
        $(toastElement).find('.toast-body .small').text(message);

        bootstrap.Toast.getOrCreateInstance(
            toastElement,
            { delay: 5000 }
        ).show();
    }

    function getFilteredSuppliers() {
        const search = normalizeText(state.search.trim());

        return suppliers
            .filter(function (supplier) {
                const searchableText = normalizeText(
                    supplier.nome_razao_social + ' ' +
                    (supplier.nome_fantasia || '') + ' ' +
                    (supplier.cpf_cnpj || '') + ' ' +
                    (supplier.email || '') + ' ' +
                    (supplier.telefone || '')
                );

                const matchesSearch =
                    !search || searchableText.includes(search);

                const matchesType =
                    !state.personType ||
                    supplier.tipo_pessoa === state.personType;

                const matchesStatus =
                    !state.status ||
                    supplier.ativo === state.status;

                return matchesSearch && matchesType && matchesStatus;
            })
            .sort(function (first, second) {
                const firstName = first.nome_razao_social || '';
                const secondName = second.nome_razao_social || '';

                if (state.sort === 'name-desc') {
                    return secondName.localeCompare(firstName, 'pt-BR');
                }

                if (state.sort === 'trade-name-asc') {
                    return (first.nome_fantasia || '').localeCompare(
                        second.nome_fantasia || '',
                        'pt-BR'
                    );
                }

                return firstName.localeCompare(secondName, 'pt-BR');
            });
    }

    function createTypeBadge(personType) {
        const isCompany = personType === 'J';

        const className = isCompany
            ? 'supplier-type-company'
            : 'supplier-type-person';

        const label = isCompany
            ? 'Pessoa jurídica'
            : 'Pessoa física';

        return `
            <span class="supplier-type ${className}">
                ${label}
            </span>
        `;
    }

    function createDocumentText(supplier) {
        const document = formatDocument(
            supplier.cpf_cnpj,
            supplier.tipo_pessoa
        );

        if (!document) {
            return `
                <span class="supplier-document supplier-document-empty">
                    Não informado
                </span>
            `;
        }

        return `
            <span class="supplier-document">
                ${escapeHtml(document)}
            </span>
        `;
    }

    function createContact(supplier) {
        const email = supplier.email || 'E-mail não informado';
        const phone = supplier.telefone || 'Telefone não informado';

        return `
            <div class="supplier-contact">
                <strong>${escapeHtml(email)}</strong>
                <small>${escapeHtml(phone)}</small>
            </div>
        `;
    }

    function createStatusBadge(status) {
        const isActive = status === 'S';

        const className = isActive
            ? 'category-status-active'
            : 'category-status-inactive';

        return `
            <span class="status-badge ${className}">
                <i></i>
                ${isActive ? 'Ativo' : 'Inativo'}
            </span>
        `;
    }

    function renderRows(items) {
        const rows = items.map(function (supplier) {
            const tradeName =
                supplier.nome_fantasia || 'Sem nome fantasia';

            return `
                <tr>
                    <td>
                        <div class="category-cell">
                            <span class="category-table-icon">
                                <i class="bi bi-buildings"></i>
                            </span>
                            <div>
                                <strong>
                                    ${escapeHtml(supplier.nome_razao_social)}
                                </strong>
                                <small>${escapeHtml(tradeName)}</small>
                            </div>
                        </div>
                    </td>

                    <td>${createTypeBadge(supplier.tipo_pessoa)}</td>

                    <td>${createDocumentText(supplier)}</td>

                    <td>${createContact(supplier)}</td>

                    <td>${createStatusBadge(supplier.ativo)}</td>

                    <td class="text-end">
                        <div class="dropdown">
                            <button
                                class="icon-button row-menu"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Ações de ${escapeHtml(supplier.nome_razao_social)}"
                            >
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="fornecedor_form.php?id=${encodeURIComponent(supplier.id)}"
                                    >
                                        <i class="bi bi-pencil me-2"></i>
                                        Editar
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <li>
                                    <button
                                        class="dropdown-item text-danger demo-supplier-action"
                                        type="button"
                                    >
                                        <i class="bi bi-trash3 me-2"></i>
                                        Excluir
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        $('#supplierTableBody').html(rows);
    }

    function createPageButton(
        label,
        page,
        disabled,
        active,
        ariaLabel
    ) {
        const disabledClass = disabled ? ' disabled' : '';
        const activeClass = active ? ' active' : '';
        const disabledAttribute = disabled ? ' disabled' : '';

        return `
            <li class="page-item${disabledClass}${activeClass}">
                <button
                    class="page-link"
                    type="button"
                    data-page="${page}"
                    aria-label="${ariaLabel}"
                    ${disabledAttribute}
                >
                    ${label}
                </button>
            </li>
        `;
    }

    function renderPagination(totalItems, totalPages) {
        const $pagination = $('#supplierPagination').empty();

        $pagination.append(
            createPageButton(
                '<i class="bi bi-chevron-left"></i>',
                state.page - 1,
                state.page === 1 || totalItems === 0,
                false,
                'Página anterior'
            )
        );

        for (let page = 1; page <= totalPages; page += 1) {
            $pagination.append(
                createPageButton(
                    page,
                    page,
                    false,
                    page === state.page,
                    'Página ' + page
                )
            );
        }

        $pagination.append(
            createPageButton(
                '<i class="bi bi-chevron-right"></i>',
                state.page + 1,
                state.page === totalPages || totalItems === 0,
                false,
                'Próxima página'
            )
        );

        if (totalItems === 0) {
            $('#supplierPaginationSummary').text('0 resultados');
            return;
        }

        const firstItem = (state.page - 1) * state.pageSize + 1;
        const lastItem = Math.min(
            state.page * state.pageSize,
            totalItems
        );

        $('#supplierPaginationSummary').text(
            'Mostrando ' + firstItem + '–' + lastItem +
            ' de ' + totalItems
        );
    }

    function render() {
        const filteredSuppliers = getFilteredSuppliers();

        const totalPages = Math.max(
            1,
            Math.ceil(filteredSuppliers.length / state.pageSize)
        );

        if (state.page > totalPages) {
            state.page = totalPages;
        }

        const firstIndex = (state.page - 1) * state.pageSize;

        const pageItems = filteredSuppliers.slice(
            firstIndex,
            firstIndex + state.pageSize
        );

        const hasItems = filteredSuppliers.length > 0;

        renderRows(pageItems);
        renderPagination(filteredSuppliers.length, totalPages);

        $('#supplierTableWrapper').toggleClass('d-none', !hasItems);
        $('#supplierEmpty').toggleClass('d-none', hasItems);
    }

    function searchSuppliers() {
        state.search = $('#supplierSearch').val() || '';
        state.personType = $('#supplierTypeFilter').val() || '';
        state.status = $('#supplierStatusFilter').val() || '';
        state.sort = $('#supplierSort').val() || 'name-asc';
        state.page = 1;

        render();
    }

    function clearFilters() {
        state.search = '';
        state.personType = '';
        state.status = '';
        state.sort = 'name-asc';
        state.page = 1;

        $('#supplierSearch, #dashboardSearch').val('');
        $('#supplierTypeFilter').val('');
        $('#supplierStatusFilter').val('');
        $('#supplierSort').val('name-asc');

        render();
    }

    $sidebarToggle.on('click', function () {
        setSidebar(!$sidebar.hasClass('show'));
    });

    $backdrop.on('click', function () {
        setSidebar(false);
    });

    $(window).on('resize', function () {
        if (window.innerWidth >= 1200) {
            setSidebar(false);
        }
    });

    $('#supplierSearch').on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchSuppliers();
        }
    });

    $('#searchSupplierButton').on('click', searchSuppliers);

    $('#supplierPageSize').on('change', function () {
        state.pageSize = Number(this.value) || 5;
        state.page = 1;
        render();
    });

    $('#emptyClearSupplierFilters').on('click', clearFilters);

    $('#supplierPagination').on('click', '[data-page]', function () {
        const page = Number($(this).data('page'));

        if (!Number.isInteger(page) || page < 1) {
            return;
        }

        state.page = page;
        render();

        document.querySelector('.category-list-card').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    });

    $(document).on('click', '.demo-supplier-action', function () {
        showToast(
            'Exclusão não implementada',
            'A exclusão de fornecedores ainda não está conectada ao controller.'
        );
    });

    $(document).on('keydown', function (event) {
        if (event.key === 'Escape') {
            setSidebar(false);
        }

        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 'k'
        ) {
            event.preventDefault();
            $('#dashboardSearch').trigger('focus');
        }
    });

    // Carrega os fornecedores reais do banco de dados.
    loadSuppliers();
});
