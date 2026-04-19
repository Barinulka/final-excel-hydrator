import { Controller } from '@hotwired/stimulus';
import { storePendingToast } from './toast.js';

export default class FinancialModelConfirmActionController extends Controller {
    static targets = [
        'dialog',
        'error',
        'submit',
        'cancelButton',
    ];

    static values = {
        apiUrl: String,
    };

    connect() {
        this.defaultSubmitText = this.submitTarget.textContent.trim();
    }

    start() {
        this.clearError();
        this.dialogTarget.showModal();
        this.cancelButtonTarget.focus();
    }

    cancel(event) {
        event?.preventDefault();
        this.clearError();
        this.closeDialog();
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
            this.cancel(event);
        }
    }

    async submit(event) {
        event.preventDefault();
        this.clearError();
        this.setSubmitting(true);

        try {
            const response = await fetch(this.apiUrlValue, {
                method: this.httpMethod,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await this.readJson(response);

            if (!response.ok) {
                this.showError(this.errorMessageFor(data.error));
                return;
            }

            storePendingToast(this.successMessage);
            window.location.reload();
        } catch (error) {
            this.showError('Ошибка сети. Попробуйте еще раз.');
        } finally {
            this.setSubmitting(false);
        }
    }

    async readJson(response) {
        try {
            return await response.json();
        } catch (error) {
            return {};
        }
    }

    get httpMethod() {
        return 'PATCH';
    }

    get successMessage() {
        return 'Действие выполнено.';
    }

    get submittingText() {
        return 'Сохраняем...';
    }

    errorMessageFor() {
        return 'Не удалось выполнить действие.';
    }

    closeDialog() {
        if (this.dialogTarget.open) {
            this.dialogTarget.close();
        }
    }

    showError(message) {
        this.errorTarget.textContent = message;
        this.errorTarget.classList.add('project-form__client-error--visible');
    }

    clearError() {
        this.errorTarget.textContent = '';
        this.errorTarget.classList.remove('project-form__client-error--visible');
    }

    setSubmitting(isSubmitting) {
        this.submitTarget.disabled = isSubmitting;
        this.cancelButtonTarget.disabled = isSubmitting;
        this.submitTarget.textContent = isSubmitting ? this.submittingText : this.defaultSubmitText;
    }
}
