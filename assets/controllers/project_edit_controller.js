import { Controller } from '@hotwired/stimulus';
import { storePendingToast } from '../utils/toast.js';

export default class extends Controller {
    static targets = [
        'dialog',
        'title',
        'description',
        'titleError',
        'descriptionError',
        'formError',
        'submit',
    ];

    static values = {
        apiUrl: String,
    };

    connect() {
        this.originalTitle = this.titleTarget.value;
        this.originalDescription = this.descriptionTarget.value;
    }

    open() {
        this.clearAllErrors();
        this.dialogTarget.showModal();
        this.titleTarget.focus();
        this.titleTarget.select();
    }

    close() {
        this.clearAllErrors();
        this.titleTarget.value = this.originalTitle;
        this.descriptionTarget.value = this.originalDescription;
        this.dialogTarget.close();
    }

    backdropClick(event) {
        if (event.target !== this.dialogTarget) {
            return;
        }

        const rect = this.dialogTarget.getBoundingClientRect();
        const clickedInsideDialog =
            rect.top <= event.clientY &&
            event.clientY <= rect.top + rect.height &&
            rect.left <= event.clientX &&
            event.clientX <= rect.left + rect.width;

        if (!clickedInsideDialog) {
            this.close();
        }
    }

    async submit(event) {
        event.preventDefault();
        this.clearAllErrors();
        this.setSubmitting(true);

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(this.buildPayload()),
            });

            const data = await this.readJson(response);

            if (response.status === 422) {
                this.applyValidationErrors(data.fields ?? {});
                return;
            }

            if (!response.ok) {
                this.showError(this.formErrorTarget, this.errorMessageFor(data.error));
                return;
            }

            storePendingToast('Проект обновлен.');
            window.location.reload();
        } catch (error) {
            this.showError(this.formErrorTarget, 'Ошибка сети. Попробуйте еще раз.');
        } finally {
            this.setSubmitting(false);
        }
    }

    buildPayload() {
        return {
            title: this.titleTarget.value,
            description: this.descriptionTarget.value,
        };
    }

    async readJson(response) {
        try {
            return await response.json();
        } catch (error) {
            return {};
        }
    }

    applyValidationErrors(fields) {
        if (fields.title?.length) {
            this.showError(this.titleErrorTarget, fields.title[0]);
        }

        if (fields.description?.length) {
            this.showError(this.descriptionErrorTarget, fields.description[0]);
        }
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'project_archived') {
            return 'Архивный проект нельзя редактировать.';
        }

        if (errorCode === 'not_found') {
            return 'Проект не найден.';
        }

        return 'Не удалось обновить проект.';
    }

    clearAllErrors() {
        this.clearError(this.titleErrorTarget);
        this.clearError(this.descriptionErrorTarget);
        this.clearError(this.formErrorTarget);
    }

    showError(target, message) {
        target.textContent = message;
        target.classList.add('project-form__error--visible');
    }

    clearError(target) {
        target.textContent = '';
        target.classList.remove('project-form__error--visible');
    }

    setSubmitting(isSubmitting) {
        this.submitTarget.disabled = isSubmitting;
        this.submitTarget.textContent = isSubmitting ? 'Сохраняем...' : 'Сохранить';
    }
}
