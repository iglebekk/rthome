const copyText = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);

        return true;
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
