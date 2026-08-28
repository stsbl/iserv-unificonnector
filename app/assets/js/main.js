const authenticationMode = document.querySelector('[name="connection_settings[authenticationMode]"]');

if (authenticationMode) {
    const updateCredentialFields = () => {
        document.querySelectorAll('[data-unificonnector-credentials]').forEach((element) => {
            element.hidden = element.dataset.unificonnectorCredentials !== authenticationMode.value;
        });
    };

    authenticationMode.addEventListener('change', updateCredentialFields);
    updateCredentialFields();
}

const syncForm = document.querySelector('[data-unificonnector-sync-form]');
const syncOutput = document.querySelector('[data-unificonnector-sync-output]');

if (syncForm && syncOutput) {
    syncForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submit = syncForm.querySelector('[type="submit"]');
        submit.disabled = true;
        syncOutput.textContent = '';
        syncOutput.classList.remove('d-none');

        try {
            const response = await fetch(syncForm.action, {method: 'POST', body: new FormData(syncForm)});
            if (!response.ok || !response.body) {
                throw new Error('Synchronization could not be started.');
            }
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            while (true) {
                const {done, value} = await reader.read();
                if (done) {
                    break;
                }
                syncOutput.textContent += decoder.decode(value, {stream: true});
                syncOutput.scrollTop = syncOutput.scrollHeight;
            }
        } catch (error) {
            syncOutput.textContent += `${error instanceof Error ? error.message : 'Synchronization could not be started.'}\n`;
        } finally {
            submit.disabled = false;
        }
    });
}
