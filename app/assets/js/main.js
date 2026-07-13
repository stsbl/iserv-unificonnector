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
