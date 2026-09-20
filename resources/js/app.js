const printOnLoad = () => {
    if (document.body?.hasAttribute('data-print-on-load')) {
        window.print();
    }
};

const returnAfterPrint = () => {
    if (window.opener && ! window.opener.closed) {
        window.close();

        return;
    }

    const returnUrl = document.body?.dataset.printReturnUrl;

    if (returnUrl) {
        window.location.replace(returnUrl);
    }
};

if (document.body?.hasAttribute('data-print-on-load')) {
    window.addEventListener('afterprint', returnAfterPrint, { once: true });
}

document.addEventListener('click', (event) => {
    const printLink = event.target.closest('[data-print-link]');

    if (! printLink) {
        return;
    }

    event.preventDefault();

    const printWindow = window.open(printLink.href, '_blank');

    if (printWindow) {
        printWindow.focus();

        return;
    }

    window.location.assign(printLink.href);
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', printOnLoad, { once: true });
} else {
    printOnLoad();
}

const copyText = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            // Fall back to the legacy copy API when clipboard permissions are unavailable.
        }
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();

    const copied = document.execCommand('copy');
    textarea.remove();

    return copied;
};

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-url]');

    if (! button) {
        return;
    }

    try {
        if (await copyText(button.dataset.copyUrl)) {
            button.textContent = button.dataset.copiedLabel ?? button.textContent;
        }
    } catch {
        return;
    }
});

const invoiceCurrency = (form, amount) => new Intl.NumberFormat(
    form.dataset.locale === 'nb' ? 'nb-NO' : 'en',
    { style: 'currency', currency: 'NOK' },
).format(amount);

const updateInvoiceCreation = (form) => {
    let total = 0;

    form.querySelectorAll('[data-invoice-line]').forEach((line) => {
        const price = Number.parseInt(line.dataset.productPriceOre ?? '0', 10) / 100;
        const quantity = Number.parseFloat(line.querySelector('[data-line-quantity]')?.value.replace(',', '.') ?? '0') || 0;
        const lineTotal = price * quantity;
        const lineTotalElement = line.querySelector('[data-line-total]');

        total += lineTotal;

        if (lineTotalElement) {
            lineTotalElement.textContent = invoiceCurrency(form, lineTotal);
        }
    });

    const totalElement = form.querySelector('[data-invoice-total]');
    const submitButton = form.querySelector('[data-create-invoices]');
    const recipients = form.querySelectorAll('[data-invoice-recipient]').length;
    const invoiceCount = Math.max(recipients, 1);

    if (totalElement) {
        totalElement.textContent = invoiceCurrency(form, total);
    }

    if (submitButton) {
        submitButton.textContent = invoiceCount === 1
            ? form.dataset.singleLabel
            : form.dataset.multipleLabel.replace('__COUNT__', invoiceCount);
    }

};

const reindexInvoiceLines = (container) => {
    container.querySelectorAll('[data-invoice-line]').forEach((line, index) => {
        line.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replace(/lines\[(?:\d+|__INDEX__)]/, `lines[${index}]`);
        });
    });
};

const filterInvoicePicker = (input) => {
    const pickerType = input.dataset.pickerSearch;
    const options = document.querySelectorAll(`[data-picker-option="${pickerType}"]`);
    const query = input.value.trim().toLocaleLowerCase();

    options.forEach((option) => {
        const row = option.closest(`[data-picker-option-row="${pickerType}"]`);

        if (row) {
            row.hidden = ! row.textContent.toLocaleLowerCase().includes(query);
        }
    });
};

const syncInvoicePicker = (form, pickerType) => {
    const selectedAttribute = pickerType === 'recipient' ? 'data-recipient-id' : 'data-product-id';
    const selected = new Set([...form.querySelectorAll(`[${selectedAttribute}]`)].map((item) => item.getAttribute(selectedAttribute)));
    form._invoicePickerSelections ??= {};
    form._invoicePickerSelections[pickerType] = new Set(selected);

    document.querySelectorAll(`[data-picker-option="${pickerType}"]`).forEach((option) => {
        const checkbox = option.matches('input') ? option : option.querySelector('input[type="checkbox"]');
        const isSelected = selected.has(option.dataset.pickerId);
        if (checkbox) {
            checkbox.checked = isSelected;
            checkbox.disabled = isSelected || option.dataset.pickerUnavailable === 'true';
        }
    });
};

const appendInvoiceSelections = (form, pickerType) => {
    const container = form.querySelector(pickerType === 'recipient' ? '[data-invoice-recipients]' : '[data-invoice-lines]');
    const selectedAttribute = pickerType === 'recipient' ? 'data-recipient-id' : 'data-product-id';
    const templateAttribute = pickerType === 'recipient' ? 'data-recipient-template' : 'data-product-template';
    const existingIds = new Set([...form.querySelectorAll(`[${selectedAttribute}]`)].map((item) => item.getAttribute(selectedAttribute)));

    const selected = form._invoicePickerSelections?.[pickerType] ?? new Set();

    selected.forEach((pickerId) => {
        const option = document.querySelector(`[data-picker-option="${pickerType}"][data-picker-id="${pickerId}"]`);

        if (! option) {
            return;
        }

        if (existingIds.has(pickerId)) {
            return;
        }

        const template = form.querySelector(`[${templateAttribute}="${pickerId}"]`);

        if (container && template) {
            container.append(template.content.cloneNode(true));
            existingIds.add(pickerId);
        }
    });

    if (pickerType === 'product' && container) {
        reindexInvoiceLines(container);
    }

    updateInvoiceCreation(form);
    window.Flux?.modal(`invoice-${pickerType === 'recipient' ? 'recipients' : 'products'}`).close();
};

document.addEventListener('click', (event) => {
    const form = event.target.closest('[data-invoice-creation]') ?? document.querySelector('[data-invoice-creation]');

    if (! form) {
        return;
    }

    const pickerOption = event.target.closest('[data-picker-option]');

    if (pickerOption && ! event.target.closest('[data-add-selected-recipients], [data-add-selected-products]')) {
        const pickerType = pickerOption.dataset.pickerOption;
        const selections = form._invoicePickerSelections?.[pickerType] ?? new Set();

        if (selections.has(pickerOption.dataset.pickerId)) {
            selections.delete(pickerOption.dataset.pickerId);
        } else if (pickerOption.dataset.pickerUnavailable !== 'true') {
            selections.add(pickerOption.dataset.pickerId);
        }

        form._invoicePickerSelections ??= {};
        form._invoicePickerSelections[pickerType] = selections;
    }

    const modalTrigger = event.target.closest('[data-modal-trigger]');

    if (modalTrigger?.dataset.modalTrigger === 'invoice-recipients') {
        syncInvoicePicker(form, 'recipient');

        return;
    }

    if (modalTrigger?.dataset.modalTrigger === 'invoice-products') {
        syncInvoicePicker(form, 'product');

        return;
    }

    if (event.target.closest('[data-add-selected-recipients]')) {
        appendInvoiceSelections(form, 'recipient');

        return;
    }

    if (event.target.closest('[data-add-selected-products]')) {
        appendInvoiceSelections(form, 'product');

        return;
    }

    const removeRecipient = event.target.closest('[data-remove-invoice-recipient]');

    if (removeRecipient) {
        removeRecipient.closest('[data-invoice-recipient]').remove();
        updateInvoiceCreation(form);
    }

    const removeLine = event.target.closest('[data-remove-invoice-line]');

    if (removeLine) {
        const lines = form.querySelector('[data-invoice-lines]');
        removeLine.closest('[data-invoice-line]').remove();
        reindexInvoiceLines(lines);
        updateInvoiceCreation(form);
    }
});

document.addEventListener('input', (event) => {
    const form = event.target.closest('[data-invoice-creation]') ?? document.querySelector('[data-invoice-creation]');

    if (! form) {
        return;
    }

    if (event.target.matches('[data-picker-search]')) {
        filterInvoicePicker(event.target);
    }

    updateInvoiceCreation(form);
});

document.querySelectorAll('[data-invoice-creation]').forEach(updateInvoiceCreation);

const showBrregError = (element, message) => {
    element.textContent = message;
    element.hidden = false;
};

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-brreg-lookup]');

    if (! button) {
        return;
    }

    const form = button.closest('form');
    const organizationNumberInput = form?.querySelector('[data-brreg-organization-number]');
    const errorElement = form?.querySelector('[data-brreg-error]');

    if (! organizationNumberInput || ! errorElement) {
        return;
    }

    errorElement.hidden = true;

    const organizationNumber = organizationNumberInput.value.replace(/\s+/g, '');

    if (! organizationNumber) {
        showBrregError(errorElement, button.dataset.brregRequiredMessage);

        return;
    }

    const originalLabel = button.textContent;
    button.disabled = true;
    button.textContent = button.dataset.brregLoadingLabel;

    try {
        const response = await fetch(
            button.dataset.brregLookupUrl.replace('ORGANIZATION_NUMBER', encodeURIComponent(organizationNumber)),
            { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } },
        );
        const body = await response.json();

        if (! response.ok) {
            showBrregError(errorElement, body.message ?? button.dataset.brregUnavailableMessage);

            return;
        }

        const fields = Object.entries(body.data);
        const hasExistingDetails = fields.some(([name]) => form.querySelector(`[name="${name}"]`)?.value);

        if (hasExistingDetails && ! window.confirm(button.dataset.brregConfirmation)) {
            return;
        }

        organizationNumberInput.value = organizationNumber;

        fields.forEach(([name, value]) => {
            const input = form.querySelector(`[name="${name}"]`);

            if (input) {
                input.value = value ?? '';
            }
        });
    } catch {
        showBrregError(errorElement, button.dataset.brregUnavailableMessage);
    } finally {
        button.disabled = false;
        button.textContent = originalLabel;
    }
});
