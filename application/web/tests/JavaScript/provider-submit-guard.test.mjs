import test from 'node:test';
import assert from 'node:assert/strict';

function installInFlightSubmitGuard(forms) {
    for (const form of forms) {
        form.onSubmit(() => {
            const submit = form.submitButton;
            if (!submit) return;
            submit.disabled = true;
            submit.ariaDisabled = 'true';
        }, { once: true });
    }
}

function fakeForm() {
    const listeners = [];
    const submitButton = { disabled: false, ariaDisabled: null };

    return {
        submitButton,
        onSubmit(callback, options) {
            listeners.push({ callback, options });
        },
        submit() {
            const listener = listeners[0];
            if (!listener) return;
            listener.callback();
            if (listener.options.once) listeners.shift();
        },
    };
}

test('in-flight sensitive submit disables repeated submission', () => {
    const form = fakeForm();
    installInFlightSubmitGuard([form]);

    form.submit();
    assert.equal(form.submitButton.disabled, true);
    assert.equal(form.submitButton.ariaDisabled, 'true');

    // A second browser click cannot re-enable or trigger a second active control.
    form.submit();
    assert.equal(form.submitButton.disabled, true);
    assert.equal(form.submitButton.ariaDisabled, 'true');
});

test('forms without a submit control remain safe', () => {
    const form = fakeForm();
    form.submitButton = null;
    assert.doesNotThrow(() => installInFlightSubmitGuard([form]));
    assert.doesNotThrow(() => form.submit());
});
