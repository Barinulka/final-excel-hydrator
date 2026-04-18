import { Controller } from '@hotwired/stimulus';
import { showToast } from '../utils/toast.js';

export default class extends Controller {
    static targets = [
        'title',
        'startButton',
        'dialog',
        'input',
        'error',
        'submit',
        'cancelButton',
    ];

    static values = {
        apiUrl: String,
    };

    connect() {
        this.currentTitle = this.titleTarget.textContent.trim();
    }

    start() {
        this.clearError();
        this.currentTitle = this.titleTarget.textContent.trim();
        this.inputTarget.value = this.currentTitle;
        this.element.classList.add('model-card--renaming');
        this.dialogTarget.showModal();

        window.requestAnimationFrame(() => {
            this.inputTarget.focus();
            this.inputTarget.select();
        });
    }

    cancel(event) {
        event?.preventDefault();

        this.clearError();
        this.inputTarget.value = this.currentTitle;
        this.closeEditor();
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

        const title = this.inputTarget.value.trim();
        if (!title) {
            this.showError('Введите название модели.');
            return;
        }

        if (title === this.currentTitle) {
            this.closeEditor();
            return;
        }

        this.setSubmitting(true);

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ title }),
            });

            const data = await this.readJson(response);

            if (response.status === 422) {
                this.applyValidationErrors(data.fields ?? {});
                return;
            }

            if (!response.ok) {
                this.showError(this.errorMessageFor(data.error));
                return;
            }

            const savedTitle = data.data?.title;
            if (!savedTitle) {
                this.showError('Сервер вернул некорректный ответ.');
                return;
            }

            this.currentTitle = savedTitle;
            this.titleTarget.textContent = savedTitle;
            this.inputTarget.value = savedTitle;
            this.closeEditor();
            showToast('Финансовая модель переименована.');
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

    applyValidationErrors(fields) {
        if (fields.title?.length) {
            this.showError(fields.title[0]);
            return;
        }

        this.showError('Проверьте название модели.');
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'financial_model_archived') {
            return 'Архивную модель нельзя переименовать.';
        }

        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        return 'Не удалось переименовать финансовую модель.';
    }

    closeEditor() {
        if (this.dialogTarget.open) {
            this.dialogTarget.close();
        }

        this.element.classList.remove('model-card--renaming');
        this.startButtonTarget.focus();
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
        this.inputTarget.disabled = isSubmitting;
        this.submitTarget.disabled = isSubmitting;
        this.cancelButtonTarget.disabled = isSubmitting;
        this.submitTarget.textContent = isSubmitting ? 'Сохраняем...' : 'Сохранить';
    }
}
