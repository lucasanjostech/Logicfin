$(function () {
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

    function configurePersonType() {
        const personType = $('#supplierPersonType').val();
        const isCompany = personType === 'J';
        const documentLimit = isCompany ? 14 : 11;
        const documentName = isCompany ? 'CNPJ' : 'CPF';

        const currentDocument = $('#supplierDocument')
            .val()
            .replace(/\D/g, '')
            .slice(0, documentLimit);

        $('#supplierNameLabel').html(
            (isCompany ? 'Razão social' : 'Nome completo') +
            ' <span class="required-mark">*</span>'
        );

        // CPF e CNPJ são obrigatórios.
        $('#supplierDocumentLabel').html(
            documentName +
            ' <span class="required-mark">*</span>'
        );

        $('#supplierDocumentLimit').text(documentLimit);

        $('#supplierDocumentFeedback').text(
            'Informe os ' + documentLimit + ' números do ' + documentName + '.'
        );

        $('#supplierDocument')
            .prop('required', true)
            .attr('maxlength', documentLimit)
            .attr('pattern', '[0-9]{' + documentLimit + '}')
            .attr('placeholder', '0'.repeat(documentLimit))
            .val(currentDocument);

        $('#supplierName').attr(
            'placeholder',
            isCompany
                ? 'Ex.: Fornecedor Exemplo Ltda.'
                : 'Ex.: Maria da Silva'
        );

        $('.supplier-preview-icon i').attr(
            'class',
            isCompany ? 'bi bi-buildings' : 'bi bi-person'
        );

        $('#previewSupplierType').text(
            isCompany ? 'Pessoa jurídica' : 'Pessoa física'
        );
    }

    function updatePreview() {
        const personType = $('#supplierPersonType').val();
        const supplierName = $('#supplierName').val().trim();
        const tradeName = $('#supplierTradeName').val().trim();
        const documentValue = $('#supplierDocument').val().trim();
        const formattedDocument = formatDocument(documentValue, personType);
        const email = $('#supplierEmail').val().trim();
        const phone = $('#supplierPhone').val().trim();
        const isActive = $('#supplierActiveToggle').is(':checked');

        $('#previewSupplierName').text(
            supplierName || 'Nome do fornecedor'
        );

        $('#previewSupplierTradeName').text(
            tradeName || 'Sem nome fantasia'
        );

        $('#previewSupplierDocument').text(
            formattedDocument || 'Não informado'
        );

        $('#previewSupplierContact').text(
            email || phone || 'Não informado'
        );

        $('#supplierNameCount').text($('#supplierName').val().length);

        $('#supplierTradeNameCount').text(
            $('#supplierTradeName').val().length
        );

        $('#supplierDocumentCount').text(
            $('#supplierDocument').val().length
        );

        $('#supplierActive').val(isActive ? 'S' : 'N');

        $('#previewSupplierStatus')
            .html('<i></i>' + (isActive ? 'Ativo' : 'Inativo'))
            .toggleClass('category-status-active', isActive)
            .toggleClass('category-status-inactive', !isActive);
    }

    function mostrarToast(mensagem) {
        const toastElement = document.getElementById('supplierFormToast');

        if (!toastElement) {
            alert(mensagem);
            return;
        }

        $(toastElement).find('.toast-body').text(mensagem);

        bootstrap.Toast.getOrCreateInstance(
            toastElement,
            { delay: 4200 }
        ).show();
    }

    function carregarFornecedor(id) {
        $.ajax({
            url: controllerUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'findId',
                id: id
            },
            success: function (response) {
                // Aceita tanto o objeto direto quanto respostas envelopadas.
                const fornecedor =
                    response.data ||
                    response.fornecedor ||
                    response;

                if (
                    response.success === false ||
                    !fornecedor ||
                    !fornecedor.id
                ) {
                    mostrarToast(
                        response.message ||
                        response.error ||
                        'Fornecedor não encontrado.'
                    );
                    return;
                }

                $('#supplierId').val(fornecedor.id);
                $('#supplierPersonType').val(fornecedor.tipo_pessoa || 'J');

                configurePersonType();

                $('#supplierName').val(
                    fornecedor.nome_razao_social || ''
                );

                $('#supplierTradeName').val(
                    fornecedor.nome_fantasia || ''
                );

                $('#supplierDocument').val(
                    String(fornecedor.cpf_cnpj || '').replace(/\D/g, '')
                );

                $('#supplierEmail').val(fornecedor.email || '');
                $('#supplierPhone').val(fornecedor.telefone || '');
                $('#supplierNote').val(fornecedor.observacao || '');

                $('#supplierActiveToggle').prop(
                    'checked',
                    fornecedor.ativo === 'S'
                );

                $('#supplierBreadcrumb').text('Editar');

                $('#supplierEyebrow').html(
                    '<span></span>Edição de fornecedor'
                );

                $('#supplierPageTitle').text('Editar fornecedor');

                $('#supplierPageDescription').text(
                    'Atualize os dados cadastrais e o status do fornecedor.'
                );

                $('#saveSupplierButton').html(
                    '<i class="bi bi-check-lg me-2"></i>Salvar alterações'
                );

                $('#supplierMetadata').removeClass('d-none');
                $('#supplierIdDisplay').text('#' + fornecedor.id);

                updatePreview();
            },
            error: function (xhr) {
                console.error('Erro ao carregar fornecedor:', xhr.responseText);

                mostrarToast(
                    xhr.responseJSON?.error ||
                    'Não foi possível carregar os dados do fornecedor.'
                );
            }
        });
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

    $('#supplierPersonType').on('change', function () {
        configurePersonType();
        updatePreview();
    });

    $('#supplierName, #supplierTradeName').on('input', updatePreview);

    $('#supplierDocument').on('input', function () {
        const personType = $('#supplierPersonType').val();
        const limit = personType === 'J' ? 14 : 11;

        this.value = this.value.replace(/\D/g, '').slice(0, limit);

        updatePreview();
    });

    $('#supplierEmail, #supplierPhone, #supplierNote').on(
        'input',
        updatePreview
    );

    $('#supplierActiveToggle').on('change', updatePreview);

    $('#supplierForm').on('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        $(this).addClass('was-validated');

        const documentValue = $('#supplierDocument').val().replace(/\D/g, '');
        const personType = $('#supplierPersonType').val();
        const expectedLength = personType === 'J' ? 14 : 11;
        const documentName = personType === 'J' ? 'CNPJ' : 'CPF';

        // Exige o documento e a quantidade correta de dígitos.
        if (documentValue.length !== expectedLength) {
            $('#supplierDocument')
                .val(documentValue)
                .trigger('input')
                .trigger('focus');

            $('#supplierDocument')[0].setCustomValidity(
                'Informe os ' + expectedLength + ' números do ' + documentName + '.'
            );

            this.reportValidity();
            return;
        }

        $('#supplierDocument')[0].setCustomValidity('');

        if (!this.checkValidity()) {
            this.querySelector(':invalid').focus();
            this.reportValidity();
            return;
        }

        const id = $('#supplierId').val();

        const dados = {
            action: id ? 'update' : 'create',
            id: id,
            nome_razao_social: $('#supplierName').val().trim(),
            nome_fantasia: $('#supplierTradeName').val().trim(),
            tipo_pessoa: personType,
            cpf_cnpj: documentValue,
            email: $('#supplierEmail').val().trim(),
            telefone: $('#supplierPhone').val().trim(),
            observacao: $('#supplierNote').val().trim(),
            ativo: $('#supplierActive').val()
        };

        const $botaoSalvar = $('#saveSupplierButton');
        const textoOriginal = $botaoSalvar.html();

        $botaoSalvar.prop('disabled', true).text('Salvando...');

        $.ajax({
            url: controllerUrl,
            method: 'POST',
            dataType: 'json',
            data: dados,
            success: function (response) {
                if (
                    response.success === false ||
                    response.error
                ) {
                    mostrarToast(
                        response.message ||
                        response.error ||
                        'Não foi possível salvar o fornecedor.'
                    );
                    return;
                }

                mostrarToast(
                    response.message ||
                    (id
                        ? 'Fornecedor atualizado com sucesso!'
                        : 'Fornecedor cadastrado com sucesso!')
                );

                setTimeout(function () {
                    window.location.href = './fornecedores.php';
                }, 1200);
            },
            error: function (xhr) {
                console.error('Erro ao salvar fornecedor:', xhr.responseText);

                mostrarToast(
                    xhr.responseJSON?.error ||
                    xhr.responseJSON?.message ||
                    'Erro ao comunicar com o servidor.'
                );
            },
            complete: function () {
                $botaoSalvar.prop('disabled', false).html(textoOriginal);
            }
        });
    });

    // Remove a mensagem de validação personalizada quando o usuário altera o documento.
    $('#supplierDocument').on('input', function () {
        this.setCustomValidity('');
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

    configurePersonType();

    const parameters = new URLSearchParams(window.location.search);
    const id = Number(parameters.get('id'));

    if (id > 0) {
        carregarFornecedor(id);
    }

    updatePreview();
});
