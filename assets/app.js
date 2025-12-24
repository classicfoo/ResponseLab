document.addEventListener('DOMContentLoaded', () => {
    const choiceInputs = document.querySelectorAll('input[name="choice"]');
    const feedbackAreas = document.querySelectorAll('[data-feedback]');
    const anonToggle = document.querySelector('#anonymous-toggle');
    const identityFields = document.querySelector('#identity-fields');

    const updateFeedback = () => {
        const selected = document.querySelector('input[name="choice"]:checked');
        feedbackAreas.forEach((area) => {
            const target = area.getAttribute('data-feedback');
            const textarea = area.querySelector('textarea');
            if (selected && selected.value === target) {
                area.classList.remove('hidden');
                if (textarea) {
                    textarea.disabled = false;
                }
            } else {
                area.classList.add('hidden');
                if (textarea) {
                    textarea.disabled = true;
                }
            }
        });
    };

    const updateIdentity = () => {
        if (!anonToggle || !identityFields) {
            return;
        }
        if (anonToggle.checked) {
            identityFields.classList.add('hidden');
        } else {
            identityFields.classList.remove('hidden');
        }
    };

    choiceInputs.forEach((input) => {
        input.addEventListener('change', updateFeedback);
    });

    if (anonToggle) {
        anonToggle.addEventListener('change', updateIdentity);
    }

    updateFeedback();
    updateIdentity();
});
