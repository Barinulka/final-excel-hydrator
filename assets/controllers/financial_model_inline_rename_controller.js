import { Controller } from '@hotwired/stimulus';
import { showToast } from '../utils/toast.js';

export default class extends Controller {
    static targets = ['input', 'pageTitle', 'sidebarTitle', 'summaryMeta', 'error'];

    static values = {
        apiUrl: String,
    };

    connect() {
        this.originalTitle = this.inputTarget.value.trim();
        this.isSaving = false;
        this.syncDisplays(this.originalTitle);
    }

    sync() {
        this.clearError();
        this.syncDisplays(this.inputTarget.value.trim());
    }

    submitOnEnter(event) {
        event.preventDefault();
        this.save();
    }

    async save() {
        if (this.isSaving || this.inputTarget.disabled) {
            return;
        }

        const title = this.inputTarget.value.trim();

        if (title === '') {
            this.showError('Введите название модели.');
            this.syncDisplays(this.originalTitle);
            return;
        }

        if (title === this.originalTitle) {
            this.syncDisplays(title);
            return;
        }

        this.isSaving = true;
        this.inputTarget.disabled = true;

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ title }),
            });

            const data = await this.readJson(response);

            if (response.status === 422) {
                this.applyValidationErrors(data.fields ?? {});
                this.syncDisplays(this.originalTitle);
                return;
            }

            if (!response.ok) {
                this.showError(this.errorMessageFor(data.error));
                this.syncDisplays(this.originalTitle);
                return;
            }

            const savedTitle = data.data?.title;
            if (!savedTitle) {
                this.showError('Сервер вернул некорректный ответ.');
                this.syncDisplays(this.originalTitle);
                return;
            }

            this.originalTitle = savedTitle;
            this.inputTarget.value = savedTitle;
            this.syncDisplays(savedTitle);
            showToast('Название модели обновлено.');
        } catch (error) {
            this.showError('Ошибка сети. Попробуйте еще раз.');
            this.syncDisplays(this.originalTitle);
        } finally {
            this.inputTarget.disabled = false;
            this.isSaving = false;
        }
    }

    syncDisplays(title) {
        const displayTitle = title !== '' ? title : 'Новая модель';

        if (this.hasPageTitleTarget) {
            this.pageTitleTarget.textContent = displayTitle;
        }

        if (this.hasSidebarTitleTarget) {
            this.sidebarTitleTarget.textContent = displayTitle;
        }

        if (this.hasSummaryMetaTarget) {
            this.summaryMetaTarget.textContent = displayTitle;
        }
    }

    applyValidationErrors(fields) {
        if (fields.title?.length) {
            this.showError(fields.title[0]);
            return;
        }

        this.showError('Не удалось сохранить название модели.');
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'financial_model_archived') {
            return 'Архивную модель нельзя переименовать.';
        }

        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        return 'Не удалось сохранить название модели.';
    }

    async readJson(response) {
        try {
            return await response.json();
        } catch (error) {
            return {};
        }
    }

    showError(message) {
        if (!this.hasErrorTarget) {
            return;
        }

        this.errorTarget.textContent = message;
        this.errorTarget.classList.add('project-form__client-error--visible');
    }

    clearError() {
        if (!this.hasErrorTarget) {
            return;
        }

        this.errorTarget.textContent = '';
        this.errorTarget.classList.remove('project-form__client-error--visible');
    }
}
