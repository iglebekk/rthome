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
