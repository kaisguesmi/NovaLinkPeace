(function () {
    'use strict';

    var AUTO_HIDE_MS = 4000;

    function trim(value) {
        return value ? value.replace(/^\s+|\s+$/g, '') : '';
    }

    function showError(input, message) {
        if (!input) {
            return;
        }

        input.classList.add('error-input');

        var errorEl = input.nextElementSibling;
        if (!errorEl || !errorEl.classList || !errorEl.classList.contains('error-message')) {
            errorEl = document.createElement('span');
            errorEl.className = 'error-message';
            input.parentNode.insertBefore(errorEl, input.nextSibling);
        }

        errorEl.textContent = message || '';
        if (message) {
            errorEl.style.display = 'inline-block';
            errorEl.style.opacity = '1';
        } else {
            errorEl.style.opacity = '0';
            errorEl.style.display = 'none';
        }

        if (input._errorTimeout) {
            clearTimeout(input._errorTimeout);
        }

        if (message) {
            input._errorTimeout = setTimeout(function () {
                hideError(input);
            }, AUTO_HIDE_MS);
        }
    }

    function hideError(input) {
        if (!input) {
            return;
        }
        input.classList.remove('error-input');
        var errorEl = input.nextElementSibling;
        if (errorEl && errorEl.classList && errorEl.classList.contains('error-message')) {
            errorEl.style.opacity = '0';
            errorEl.style.display = 'none';
            errorEl.textContent = '';
        }
        if (input._errorTimeout) {
            clearTimeout(input._errorTimeout);
            input._errorTimeout = null;
        }
    }

    function clearError(input) {
        hideError(input);
    }

    function clearAllErrors(form) {
        if (!form) {
            return;
        }

        var inputs = form.querySelectorAll('.error-input');
        for (var i = 0; i < inputs.length; i++) {
            hideError(inputs[i]);
        }

        var messages = form.querySelectorAll('.error-message');
        for (var j = 0; j < messages.length; j++) {
            messages[j].style.display = 'none';
            messages[j].style.opacity = '0';
            messages[j].textContent = '';
        }
    }

    function isValidEmail(value) {
        var email = trim(value);
        if (!email) {
            return false;
        }
        var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    }

    function isValidName(value) {
        var name = trim(value);
        return name.length >= 3;
    }

    function isNumeric(value) {
        var v = trim(value);
        if (!v) {
            return false;
        }
        return /^\d+$/.test(v);
    }

    function addInputClear(input) {
        if (!input) {
            return;
        }
        input.addEventListener('input', function () {
            clearError(input);
        });
    }

    function setupLoginForm(form) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var emailInput = form.querySelector('input[name="email"]');
        var passwordInput = form.querySelector('input[name="password"]');

        addInputClear(emailInput);
        addInputClear(passwordInput);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;

            var email = trim(emailInput && emailInput.value);
            var password = trim(passwordInput && passwordInput.value);

            if (!email) {
                showError(emailInput, 'Email requis.');
                isValid = false;
            } else if (!isValidEmail(email)) {
                showError(emailInput, 'Format d\'email invalide.');
                isValid = false;
            }

            if (!password) {
                showError(passwordInput, 'Mot de passe requis.');
                isValid = false;
            } else if (password.length < 6) {
                showError(passwordInput, 'Le mot de passe doit contenir au moins 6 caractères.');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupRegisterForm(form) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var nameInput = form.querySelector('input[name="nom_complet"]');
        var emailInput = form.querySelector('input[name="email"]');
        var passwordInput = form.querySelector('input[name="password"]');
        var confirmInput = form.querySelector('input[name="password_confirm"]');
        var bioTextarea = form.querySelector('textarea[name="bio"]');

        addInputClear(nameInput);
        addInputClear(emailInput);
        addInputClear(passwordInput);
        addInputClear(confirmInput);
        addInputClear(bioTextarea);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;

            var name = trim(nameInput && nameInput.value);
            var email = trim(emailInput && emailInput.value);
            var password = trim(passwordInput && passwordInput.value);
            var confirmPassword = trim(confirmInput && confirmInput.value);
            var bio = trim(bioTextarea && bioTextarea.value);

            if (!name) {
                showError(nameInput, 'Nom complet requis.');
                isValid = false;
            } else if (!isValidName(name)) {
                showError(nameInput, 'Nom complet trop court (3 caractères minimum).');
                isValid = false;
            }

            if (!email) {
                showError(emailInput, 'Email requis.');
                isValid = false;
            } else if (!isValidEmail(email)) {
                showError(emailInput, 'Format d\'email invalide.');
                isValid = false;
            }

            if (!password) {
                showError(passwordInput, 'Mot de passe requis.');
                isValid = false;
            } else if (password.length < 6) {
                showError(passwordInput, 'Le mot de passe doit contenir au moins 6 caractères.');
                isValid = false;
            }

            if (confirmInput) {
                if (!confirmPassword) {
                    showError(confirmInput, 'Veuillez confirmer le mot de passe.');
                    isValid = false;
                } else if (password && password !== confirmPassword) {
                    showError(confirmInput, 'Les mots de passe ne correspondent pas.');
                    isValid = false;
                }
            }

            if (bio && bio.length > 0 && bio.length < 10) {
                showError(bioTextarea, 'La bio doit contenir au moins 10 caractères ou être laissée vide.');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupProfileForm(form) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var nameInput = form.querySelector('input[name="nom_complet"]');
        var bioTextarea = form.querySelector('textarea[name="bio"]');

        addInputClear(nameInput);
        addInputClear(bioTextarea);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;

            var name = trim(nameInput && nameInput.value);
            var bio = trim(bioTextarea && bioTextarea.value);

            if (!name) {
                showError(nameInput, 'Nom complet requis.');
                isValid = false;
            } else if (!isValidName(name)) {
                showError(nameInput, 'Nom complet trop court (3 caractères minimum).');
                isValid = false;
            }

            if (bio && bio.length > 0 && bio.length < 10) {
                showError(bioTextarea, 'La bio doit contenir au moins 10 caractères ou être laissée vide.');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupPostForm(form) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var contentTextarea = form.querySelector('textarea[name="content"]');

        addInputClear(contentTextarea);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;

            var content = trim(contentTextarea && contentTextarea.value);

            if (!content) {
                showError(contentTextarea, 'Le contenu est requis.');
                isValid = false;
            } else if (content.length < 10) {
                showError(contentTextarea, 'Le contenu doit contenir au moins 10 caractères.');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupStoryForm(form) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var titleInput = form.querySelector('input[name="titre"]');
        var contentTextarea = form.querySelector('textarea[name="contenu"]');

        addInputClear(titleInput);
        addInputClear(contentTextarea);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;

            var title = trim(titleInput && titleInput.value);
            var content = trim(contentTextarea && contentTextarea.value);

            if (!title) {
                showError(titleInput, 'Le titre est requis.');
                isValid = false;
            } else if (title.length < 3) {
                showError(titleInput, 'Le titre doit contenir au moins 3 caractères.');
                isValid = false;
            }

            if (!content) {
                showError(contentTextarea, 'Le contenu est requis.');
                isValid = false;
            } else if (content.length < 10) {
                showError(contentTextarea, 'Le contenu doit contenir au moins 10 caractères.');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupInitiativeForm(form) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var nameInput = form.querySelector('input[name="nom"]');
        var descriptionTextarea = form.querySelector('textarea[name="description"]');
        var dateInput = form.querySelector('input[name="date_evenement"]');

        addInputClear(nameInput);
        addInputClear(descriptionTextarea);
        addInputClear(dateInput);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;

            var name = trim(nameInput && nameInput.value);
            var description = trim(descriptionTextarea && descriptionTextarea.value);
            var dateValue = trim(dateInput && dateInput.value);

            if (!name) {
                showError(nameInput, 'Nom de l\'initiative requis.');
                isValid = false;
            } else if (name.length < 3) {
                showError(nameInput, 'Le nom doit contenir au moins 3 caractères.');
                isValid = false;
            }

            if (!description) {
                showError(descriptionTextarea, 'Description requise.');
                isValid = false;
            } else if (description.length < 10) {
                showError(descriptionTextarea, 'La description doit contenir au moins 10 caractères.');
                isValid = false;
            }

            if (!dateValue) {
                showError(dateInput, 'Date de l\'évènement requise.');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupAdminOfferForm(form) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var titleInput = form.querySelector('input[name="titre"]');
        var descriptionTextarea = form.querySelector('textarea[name="description"]');

        addInputClear(titleInput);
        addInputClear(descriptionTextarea);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;

            var title = trim(titleInput && titleInput.value);
            var description = trim(descriptionTextarea && descriptionTextarea.value);

            if (!title) {
                showError(titleInput, 'Titre requis.');
                isValid = false;
            } else if (title.length < 3) {
                showError(titleInput, 'Le titre doit contenir au moins 3 caractères.');
                isValid = false;
            }

            if (!description) {
                showError(descriptionTextarea, 'Description requise.');
                isValid = false;
            } else if (description.length < 10) {
                showError(descriptionTextarea, 'La description doit contenir au moins 10 caractères.');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupSimpleTextareaForm(form, textareaName, minLength, emptyMessage, shortMessage) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var textarea = form.querySelector('textarea[name="' + textareaName + '"]');

        addInputClear(textarea);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;

            var value = trim(textarea && textarea.value);

            if (!value) {
                showError(textarea, emptyMessage);
                isValid = false;
            } else if (value.length < minLength) {
                showError(textarea, shortMessage);
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupSelectRequiredForm(form, selectName) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var select = form.querySelector('select[name="' + selectName + '"]');
        addInputClear(select);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;
            var value = select ? trim(select.value) : '';

            if (!value) {
                showError(select, 'Veuillez sélectionner une valeur.');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupReclamationCreateForm(form) {
        if (!form) {
            return;
        }
        form.setAttribute('novalidate', 'novalidate');
        var descriptionTextarea = form.querySelector('textarea[name="description_personnalisee"]');
        var causesSelect = form.querySelector('select[name="causes[]"]');

        addInputClear(descriptionTextarea);
        addInputClear(causesSelect);

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);
            var isValid = true;

            var description = trim(descriptionTextarea && descriptionTextarea.value);
            var hasCause = false;

            if (causesSelect) {
                for (var i = 0; i < causesSelect.options.length; i++) {
                    if (causesSelect.options[i].selected) {
                        hasCause = true;
                        break;
                    }
                }
            }

            if (!hasCause) {
                showError(causesSelect, 'Sélectionnez au moins une cause.');
                isValid = false;
            }

            if (!description) {
                showError(descriptionTextarea, 'Description requise.');
                isValid = false;
            } else if (description.length < 10) {
                showError(descriptionTextarea, 'La description doit contenir au moins 10 caractères.');
                isValid = false;
            }

            if (isValid) {
                form.submit();
            } else {
                var firstError = form.querySelector('.error-input');
                if (firstError && typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        });
    }

    function setupAdminModerationForms(container) {
        if (!container) {
            return;
        }
        var rejectForms = container.querySelectorAll('form[action*="action=rejectPost"]');
        for (var i = 0; i < rejectForms.length; i++) {
            (function (form) {
                form.setAttribute('novalidate', 'novalidate');
                var reasonInput = form.querySelector('input[name="rejection_reason"]');
                addInputClear(reasonInput);

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    clearAllErrors(form);
                    var isValid = true;
                    var reason = trim(reasonInput && reasonInput.value);

                    if (!reason) {
                        showError(reasonInput, 'Veuillez saisir une raison de rejet.');
                        isValid = false;
                    }

                    if (isValid) {
                        form.submit();
                    } else {
                        var firstError = form.querySelector('.error-input');
                        if (firstError && typeof firstError.focus === 'function') {
                            firstError.focus();
                        }
                    }
                });
            })(rejectForms[i]);
        }
    }

    function submitComment(event, postId) {
        if (!event) {
            return true;
        }
        var form = event.target || event.srcElement;
        if (!form) {
            return true;
        }
        form.setAttribute('novalidate', 'novalidate');
        clearAllErrors(form);

        var input = form.querySelector('textarea[name="contenu"]') || form.querySelector('input[name="contenu"]');
        var value = trim(input && input.value);

        if (!value) {
            event.preventDefault();
            showError(input, 'Commentaire requis.');
            if (input && typeof input.focus === 'function') {
                input.focus();
            }
            return false;
        }

        if (value.length < 3) {
            event.preventDefault();
            showError(input, 'Le commentaire doit contenir au moins 3 caractères.');
            if (input && typeof input.focus === 'function') {
                input.focus();
            }
            return false;
        }

        return true;
    }

    window.submitComment = submitComment;

    function initValidation() {
        var loginForm = document.getElementById('login-form');
        if (loginForm) {
            setupLoginForm(loginForm);
        }

        var registerForm = document.getElementById('register-form');
        if (registerForm) {
            setupRegisterForm(registerForm);
        }

        var profileForm = document.getElementById('profile-form');
        if (profileForm) {
            setupProfileForm(profileForm);
        }

        var postCreatePageForm = document.getElementById('post-create-page-form');
        if (postCreatePageForm) {
            setupPostForm(postCreatePageForm);
        }

        var postEditForm = document.getElementById('post-edit-form');
        if (postEditForm) {
            setupPostForm(postEditForm);
        }

        var postInlineForm = document.getElementById('post-inline-form');
        if (postInlineForm) {
            setupPostForm(postInlineForm);
        }

        var storyCreateForm = document.getElementById('story-create-form');
        if (storyCreateForm) {
            setupStoryForm(storyCreateForm);
        }

        var storyEditForm = document.getElementById('story-edit-form');
        if (storyEditForm) {
            setupStoryForm(storyEditForm);
        }

        var initiativeCreateForm = document.getElementById('initiative-create-form');
        if (initiativeCreateForm) {
            setupInitiativeForm(initiativeCreateForm);
        }

        var initiativeEditForm = document.getElementById('initiative-edit-form');
        if (initiativeEditForm) {
            setupInitiativeForm(initiativeEditForm);
        }

        var candidatureCreateForm = document.getElementById('candidature-create-form');
        if (candidatureCreateForm) {
            setupSimpleTextareaForm(
                candidatureCreateForm,
                'motivation',
                10,
                'Motivation requise.',
                'La motivation doit contenir au moins 10 caractères.'
            );
        }

        var candidatureEditForm = document.getElementById('candidature-edit-form');
        if (candidatureEditForm) {
            setupSelectRequiredForm(candidatureEditForm, 'statut');
        }

        var postCommentCreateForm = document.getElementById('comment-create-form');
        if (postCommentCreateForm) {
            setupSimpleTextareaForm(
                postCommentCreateForm,
                'content',
                3,
                'Commentaire requis.',
                'Le commentaire doit contenir au moins 3 caractères.'
            );
        }

        var postCommentEditForm = document.getElementById('comment-edit-form');
        if (postCommentEditForm) {
            setupSimpleTextareaForm(
                postCommentEditForm,
                'content',
                3,
                'Commentaire requis.',
                'Le commentaire doit contenir au moins 3 caractères.'
            );
        }

        var storyCommentCreateForm = document.getElementById('story-comment-create-form');
        if (storyCommentCreateForm) {
            setupSimpleTextareaForm(
                storyCommentCreateForm,
                'contenu',
                3,
                'Commentaire requis.',
                'Le commentaire doit contenir au moins 3 caractères.'
            );
        }

        var storyCommentEditForm = document.getElementById('story-comment-edit-form');
        if (storyCommentEditForm) {
            setupSimpleTextareaForm(
                storyCommentEditForm,
                'contenu',
                3,
                'Commentaire requis.',
                'Le commentaire doit contenir au moins 3 caractères.'
            );
        }

        var storyShowCommentForm = document.getElementById('story-show-comment-form');
        if (storyShowCommentForm) {
            setupSimpleTextareaForm(
                storyShowCommentForm,
                'contenu',
                3,
                'Commentaire requis.',
                'Le commentaire doit contenir au moins 3 caractères.'
            );
        }

        var reclamationCreateForm = document.getElementById('reclamation-create-form');
        if (reclamationCreateForm) {
            setupReclamationCreateForm(reclamationCreateForm);
        }

        var reclamationEditForm = document.getElementById('reclamation-edit-form');
        if (reclamationEditForm) {
            setupSelectRequiredForm(reclamationEditForm, 'statut');
        }

        var adminModerationContainer = document.getElementById('admin-moderation-container');
        if (adminModerationContainer) {
            setupAdminModerationForms(adminModerationContainer);
        }

        var adminOfferCreateForm = document.getElementById('admin-offer-create-form');
        if (adminOfferCreateForm) {
            setupAdminOfferForm(adminOfferCreateForm);
        }

        var adminOfferEditForm = document.getElementById('admin-offer-edit-form');
        if (adminOfferEditForm) {
            setupAdminOfferForm(adminOfferEditForm);
        }

        var adminRejectStoryForm = document.getElementById('admin-reject-story-form');
        if (adminRejectStoryForm) {
            setupSimpleTextareaForm(
                adminRejectStoryForm,
                'rejection_reason',
                5,
                'Raison requise.',
                'La raison doit contenir au moins 5 caractères.'
            );
        }
    }

    document.addEventListener('DOMContentLoaded', initValidation);
})();
