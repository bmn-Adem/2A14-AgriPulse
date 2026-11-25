/**
 * public/assets/js/validation.js
 * Contrôle de saisie JavaScript temps réel & commentaires explicatifs sous chaque champ.
 */
document.addEventListener('DOMContentLoaded', () => {
    const regexName = /^[a-zA-ZÀ-ÿ\s\-]{2,80}$/;
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const regexPhoneTN = /^[24579]\d{7}$/;
    const regexCinTN = /^[01]\d{7}$/;
    const regexMfTN = /^\d{7}[\/\-]?[A-Z][\/\-]?[A-Z0-9]{0,4}[\/\-]?\d{0,3}$/i;
    const regexStrongPwd = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#_\-]).{8,}$/;

    function setFieldFeedback(input, isValid, message) {
        if (!input) return;
        const group = input.closest('.form-group') || input.parentElement;
        let feedback = group.querySelector('.field-feedback');
        if (!feedback) {
            feedback = document.createElement('small');
            feedback.className = 'field-feedback d-block mt-1 fw-semibold';
            group.appendChild(feedback);
        }
        input.classList.remove('is-valid', 'is-invalid');
        if (message === '') {
            feedback.textContent = '';
            return;
        }
        input.classList.add(isValid ? 'is-valid' : 'is-invalid');
        feedback.style.color = isValid ? '#198754' : '#dc3545';
        feedback.textContent = (isValid ? '✓ ' : '✕ ') + message;
    }

    document.querySelectorAll('input[data-validate="name"]').forEach((input) => {
        input.addEventListener('input', () => {
            if (/\d/.test(input.value)) {
                input.value = input.value.replace(/\d/g, '');
                setFieldFeedback(input, false, 'Les chiffres ne sont pas autorisés dans ce champ (lettres uniquement).');
                return;
            }
            const ok = regexName.test(input.value.trim());
            setFieldFeedback(
                input,
                ok,
                ok ? 'Format valide (lettres uniquement).' : 'Minimum 2 lettres requises (aucun chiffre ni symbole).'
            );
        });
    });

    document.querySelectorAll('input[data-validate="cin"]').forEach((input) => {
        input.addEventListener('input', () => {
            if (/\D/.test(input.value)) {
                input.value = input.value.replace(/\D/g, '');
                setFieldFeedback(input, false, 'La CIN tunisienne ne peut contenir que des chiffres (aucune lettre).');
                return;
            }
            if (input.value.length > 8) input.value = input.value.slice(0, 8);
            const ok = regexCinTN.test(input.value);
            setFieldFeedback(
                input,
                ok,
                ok ? 'CIN tunisienne valide (8 chiffres).' : 'Exactement 8 chiffres requis (commençant par 0 ou 1).'
            );
        });
    });

    document.querySelectorAll('input[data-validate="phone"]').forEach((input) => {
        input.addEventListener('input', () => {
            if (/\D/.test(input.value)) {
                input.value = input.value.replace(/\D/g, '');
                setFieldFeedback(input, false, 'Le numéro de téléphone ne doit contenir que des chiffres.');
                return;
            }
            if (input.value.length > 8) input.value = input.value.slice(0, 8);
            const ok = regexPhoneTN.test(input.value);
            setFieldFeedback(
                input,
                ok,
                ok ? 'Numéro tunisien valide (8 chiffres).' : '8 chiffres requis (commençant par 2, 4, 5, 7 ou 9).'
            );
        });
    });

    document.querySelectorAll('input[data-validate="email"]').forEach((input) => {
        input.addEventListener('input', () => {
            const val = input.value.trim();
            const ok = regexEmail.test(val);
            setFieldFeedback(
                input,
                ok,
                ok ? 'Adresse email valide.' : 'Format attendu : exemple@domaine.tn'
            );
        });
    });

    document.querySelectorAll('input[data-validate="mf"]').forEach((input) => {
        input.addEventListener('input', () => {
            const val = input.value.trim().toUpperCase();
            const ok = regexMfTN.test(val);
            setFieldFeedback(
                input,
                ok,
                ok ? 'Matricule fiscal valide.' : 'Format tunisien requis (ex: 1548920/A/M/000).'
            );
        });
    });

    const pwdInput = document.querySelector('input[data-validate="password"]');
    const confirmInput = document.querySelector('input[data-validate="confirm_password"]');

    if (pwdInput) {
        pwdInput.addEventListener('input', () => {
            const v = pwdInput.value;
            const rules = [
                { ok: v.length >= 8, label: '8 caractères min.' },
                { ok: /[A-Z]/.test(v), label: '1 majuscule' },
                { ok: /[a-z]/.test(v), label: '1 minuscule' },
                { ok: /\d/.test(v), label: '1 chiffre' },
                { ok: /[@$!%*?&#_\-]/.test(v), label: '1 caractère spécial (@$!%*?&#_-)' },
            ];
            const missing = rules.filter((r) => !r.ok).map((r) => r.label);
            const allOk = regexStrongPwd.test(v);
            setFieldFeedback(
                pwdInput,
                allOk,
                allOk ? 'Mot de passe fort et sécurisé.' : 'Manque : ' + missing.join(', ')
            );
            if (confirmInput && confirmInput.value !== '') {
                confirmInput.dispatchEvent(new Event('input'));
            }
        });
    }

    if (confirmInput && pwdInput) {
        confirmInput.addEventListener('input', () => {
            const ok = confirmInput.value !== '' && confirmInput.value === pwdInput.value;
            setFieldFeedback(
                confirmInput,
                ok,
                ok ? 'Les mots de passe correspondent.' : 'Les deux mots de passe ne sont pas identiques.'
            );
        });
    }

    document.querySelectorAll('input[type="file"][data-validate="justificatif"]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) {
                setFieldFeedback(input, false, 'Veuillez joindre le document justificatif obligatoire.');
                return;
            }
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            const maxBytes = 5 * 1024 * 1024;
            if (!allowedTypes.includes(file.type)) {
                input.value = '';
                setFieldFeedback(input, false, 'Format refusé. Seuls JPG, PNG et PDF sont autorisés.');
                return;
            }
            if (file.size > maxBytes) {
                input.value = '';
                setFieldFeedback(input, false, 'Fichier trop volumineux (maximum 5 Mo autorisé).');
                return;
            }
            setFieldFeedback(input, true, `Document prêt : ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} Mo)`);
        });
    });

    const profileRadios = document.querySelectorAll('input[name="profile_type"]');
    const sections = {
        agriculteur: document.getElementById('section-agriculteur'),
        client_b2c: document.getElementById('section-client_b2c'),
        client_b2b: document.getElementById('section-client_b2b'),
    };

    function updateProfileSections() {
        const checked = document.querySelector('input[name="profile_type"]:checked');
        const current = checked ? checked.value : 'client_b2c';
        Object.entries(sections).forEach(([key, el]) => {
            if (!el) return;
            const active = key === current;
            el.style.display = active ? 'block' : 'none';
            el.querySelectorAll('input, select').forEach((field) => {
                if (field.dataset.requiredFor === key) {
                    field.required = active;
                } else if (field.dataset.requiredFor) {
                    field.required = false;
                }
            });
        });
    }

    if (profileRadios.length > 0) {
        profileRadios.forEach((r) => r.addEventListener('change', updateProfileSections));
        updateProfileSections();
    }
});
