// Contrôle de saisie en JavaScript (la validation HTML5 est interdite dans le projet)
// Chaque champ indique ses règles dans data-rules, par exemple "required|email"

const rules = {
    required: (value) => value.trim() !== '' || 'Ce champ est obligatoire.',
    min: (value, n) => value.trim().length >= Number(n) || 'Au moins ' + n + ' caractères.',
    email: (value) => /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(value) || 'Adresse email invalide.',
    password: (value) => (value.length >= 8 && /[0-9]/.test(value)) || 'Au moins 8 caractères, dont un chiffre.',
    same: (value, other, form) => value === form.elements[other].value || 'Les deux mots de passe ne correspondent pas.',
};

function checkField(field) {
    const errorBox = field.parentElement.querySelector('.error');
    for (const rule of field.dataset.rules.split('|')) {
        const [name, param] = rule.split(':');
        const result = rules[name](field.value, param, field.form);
        if (result !== true) {
            errorBox.textContent = result;
            field.classList.add('invalid');
            return false;
        }
    }
    errorBox.textContent = '';
    field.classList.remove('invalid');
    return true;
}

document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        let ok = true;
        form.querySelectorAll('[data-rules]').forEach((field) => {
            if (!checkField(field)) ok = false;
        });
        if (!ok) event.preventDefault();
    });
});
