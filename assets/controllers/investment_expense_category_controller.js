import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'modal',
        'list',
        'emptyState',
        'type',
        'customTitleField',
        'customTitle',
        'relatedExpenses',
        'error',
        'submitButton',
    ];

    static values = {
        url: String,
        listUrl: String,
    };

    connect() {
        this.loadCategories();
    }

    openModal() {
        this.clearError();
        this.resetForm();
        this.modalTarget.hidden = false;
        this.toggleCustomTitle();
    }

    closeModal() {
        this.modalTarget.hidden = true;
        this.clearError();
        this.resetForm();
    }

    toggleCustomTitle() {
        const isCustom = this.typeTarget.value === 'custom';

        this.customTitleFieldTarget.hidden = !isCustom;

        if (!isCustom) {
            this.customTitleTarget.value = '';
        }
    }

    async submit(event) {
        event.preventDefault();

        this.clearError();
        this.setSubmitting(true);

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(this.buildPayload()),
            });

            const data = await this.readJson(response);

            if (!response.ok) {
                this.showError(data.error ?? 'Не удалось добавить категорию.');
                return;
            }

            this.closeModal();
            await this.loadCategories();
        } catch (error) {
            this.showError('Ошибка сети. Попробуйте еще раз.');
        } finally {
            this.setSubmitting(false);
        }
    }

    async loadCategories() {
        if (!this.hasListUrlValue) {
            return;
        }

        try {
            const response = await fetch(this.listUrlValue, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await this.readJson(response);

            if (!response.ok) {
                this.showError(data.error ?? 'Не удалось загрузить категории.');
                return;
            }

            this.renderCategories(data.items ?? []);
        } catch (error) {
            this.showError('Ошибка сети. Не удалось загрузить категории.');
        }
    }

    buildPayload() {
        const selectedType = this.typeTarget.value;

        return {
            type: selectedType === 'custom' ? null : selectedType,
            customTitle: selectedType === 'custom' ? this.customTitleTarget.value : null,
            relatedExpenses: this.relatedExpensesTarget.value,
        };
    }

    async deleteCategory(event) {
        event.preventDefault();
        event.stopPropagation();

        const button = event.currentTarget;
        const categoryId = button.dataset.categoryId;

        if (!categoryId || !this.hasListUrlValue) {
            return;
        }

        button.disabled = true;

        try {
            const response = await fetch(`${this.listUrlValue}/${encodeURIComponent(categoryId)}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await this.readJson(response);

            if (!response.ok) {
                this.showError(data.error ?? 'Не удалось удалить категорию.');
                button.disabled = false;
                return;
            }

            button.closest('.initial-investments__category')?.remove();

            if (this.listTarget.querySelectorAll('.initial-investments__category').length === 0) {
                this.showEmptyState();
            }
        } catch (error) {
            this.showError('Ошибка сети. Не удалось удалить категорию.');
            button.disabled = false;
        }
    }

    renderCategories(categories) {
        this.clearCategories();

        if (categories.length === 0) {
            this.showEmptyState();
            return;
        }

        this.hideEmptyState();

        categories.forEach((category) => {
            this.listTarget.appendChild(this.buildCategoryElement(category));
        });
    }

    appendCategory(category) {
        this.hideEmptyState();
        this.listTarget.appendChild(this.buildCategoryElement(category));
    }

    buildCategoryElement(category) {
        const item = document.createElement('article');
        item.className = 'initial-investments__category';

        const formattedExpenses = this.formatMoney(category.relatedExpenses);

        item.innerHTML = `
            <div class="initial-investments__category-main">
                <span class="initial-investments__category-label">Категория</span>
                <strong>${this.escapeHtml(category.title ?? 'Категория')}</strong>
            </div>
            <div class="initial-investments__category-expense">
                <span class="initial-investments__category-label">Расход</span>
                <strong>${this.escapeHtml(formattedExpenses ?? '—')}</strong>
            </div>
            <button
                type="button"
                class="initial-investments__category-delete"
                aria-label="Удалить категорию"
                data-category-id="${this.escapeHtml(category.id)}"
                data-action="click->investment-expense-category#deleteCategory"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M9 4h6m-8 4h10m-9 0 .7 11h6.6L16 8M10 11v5m4-5v5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                </svg>
            </button>
            <button
                type="button"
                class="initial-investments__category-toggle"
                aria-label="Раскрыть категорию"
            ></button>
        `;

        return item;
    }

    clearCategories() {
        this.listTarget
            .querySelectorAll('.initial-investments__category')
            .forEach((item) => item.remove());
    }

    showEmptyState() {
        if (this.hasEmptyStateTarget) {
            this.emptyStateTarget.hidden = false;
        }
    }

    hideEmptyState() {
        if (this.hasEmptyStateTarget) {
            this.emptyStateTarget.hidden = true;
        }
    }

    async readJson(response) {
        try {
            return await response.json();
        } catch (error) {
            return {};
        }
    }

    resetForm() {
        this.typeTarget.value = 'equipment';
        this.customTitleTarget.value = '';
        this.relatedExpensesTarget.value = '';
    }

    showError(message) {
        this.errorTarget.textContent = message;
        this.errorTarget.hidden = false;
    }

    clearError() {
        this.errorTarget.textContent = '';
        this.errorTarget.hidden = true;
    }

    setSubmitting(isSubmitting) {
        this.submitButtonTarget.disabled = isSubmitting;
        this.submitButtonTarget.textContent = isSubmitting ? 'Сохраняем...' : 'Сохранить';
    }

    formatMoney(value) {
        if (value === null || value === undefined || String(value).trim() === '') {
            return null;
        }

        const normalizedValue = String(value).replace(/[^\d]/g, '');

        if (normalizedValue === '') {
            return null;
        }

        return `${new Intl.NumberFormat('ru-RU', {
            maximumFractionDigits: 0,
        }).format(Number(normalizedValue)).replaceAll('\u00A0', ' ')} ₽`;
    }

    escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
}
