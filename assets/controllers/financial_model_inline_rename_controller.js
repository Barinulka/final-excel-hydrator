import { Controller } from '@hotwired/stimulus';
import { showToast } from '../utils/toast.js';

export default class extends Controller {
    static targets = ['titleInput', 'descriptionInput', 'pageTitle', 'sidebarTitle', 'summaryMeta', 'error'];

    static values = {
        apiUrl: String,
    };

    connect() {
        this.originalTitle = this.hasTitleInputTarget
            ? this.titleInputTarget.value.trim()
            : this.currentDisplayedTitle();
        this.originalDescription = this.hasDescriptionInputTarget ? this.descriptionInputTarget.value.trim() : '';
        this.isSaving = false;
        this.syncDisplays(this.originalTitle);
    }

    titleInputTargetConnected(element) {
        this.originalTitle = element.value.trim();
        this.syncDisplays(this.originalTitle);
    }

    descriptionInputTargetConnected(element) {
        this.originalDescription = element.value.trim();
    }

    sync() {
        if (!this.hasTitleInputTarget) {
            return;
        }

        this.clearError();
        this.syncDisplays(this.titleInputTarget.value.trim());
    }

    submit(event) {
        event.preventDefault();
        this.save();
    }

    submitOnEnter(event) {
        event.preventDefault();
        this.save();
    }

    async save() {
        if (this.isSaving || !this.hasTitleInputTarget || this.titleInputTarget.disabled) {
            return;
        }

        const title = this.titleInputTarget.value.trim();
        const description = this.hasDescriptionInputTarget ? this.descriptionInputTarget.value.trim() : '';

        if (title === '') {
            this.showError('Введите название модели.');
            this.syncDisplays(this.originalTitle);
            return;
        }

        if (title === this.originalTitle && description === this.originalDescription) {
            this.syncDisplays(title);
            return;
        }

        this.isSaving = true;
        this.titleInputTarget.disabled = true;

        if (this.hasDescriptionInputTarget) {
            this.descriptionInputTarget.disabled = true;
        }

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ title, description }),
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
            const savedDescription = data.data?.description ?? '';

            if (!savedTitle) {
                this.showError('Сервер вернул некорректный ответ.');
                this.syncDisplays(this.originalTitle);
                return;
            }

            this.originalTitle = savedTitle;
            this.originalDescription = savedDescription;
            this.titleInputTarget.value = savedTitle;

            if (this.hasDescriptionInputTarget) {
                this.descriptionInputTarget.value = savedDescription;
            }

            this.syncDisplays(savedTitle);
            showToast('Данные модели обновлены.');
        } catch (error) {
            this.showError('Ошибка сети. Попробуйте еще раз.');
            this.syncDisplays(this.originalTitle);
        } finally {
            this.titleInputTarget.disabled = false;

            if (this.hasDescriptionInputTarget) {
                this.descriptionInputTarget.disabled = false;
            }

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

        if (fields.description?.length) {
            this.showError(fields.description[0]);
            return;
        }

        this.showError('Не удалось сохранить данные модели.');
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'financial_model_archived') {
            return 'Архивную модель нельзя редактировать.';
        }

        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        return 'Не удалось сохранить данные модели.';
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

    currentDisplayedTitle() {
        if (this.hasSidebarTitleTarget) {
            return this.sidebarTitleTarget.textContent.trim();
        }

        if (this.hasSummaryMetaTarget) {
            return this.summaryMetaTarget.textContent.trim();
        }

        return '';
    }
}
