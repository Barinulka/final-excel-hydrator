import { Controller } from '@hotwired/stimulus';
import { showToast } from '../utils/toast.js';

export default class extends Controller {
    static targets = [
        'button',
        'status',
    ];

    static values = {
        apiUrl: String,
    };

    connect() {
        this.defaultButtonText = this.buttonTarget.textContent.trim();
    }

    async create(event) {
        event.preventDefault();
        this.clearStatus();
        this.setSubmitting(true);

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await this.readJson(response);

            if (!response.ok) {
                this.showStatus(this.errorMessageFor(payload.error), true);
                return;
            }

            const exportStatus = payload.data?.export?.status ?? 'pending';

            this.showStatus(`Задача создана. Статус: ${this.statusLabel(exportStatus)}.`);
            showToast('Задача Excel export создана.');
        } catch (error) {
            this.showStatus('Ошибка сети. Не удалось создать задачу export.', true);
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

    setSubmitting(isSubmitting) {
        this.buttonTarget.disabled = isSubmitting;
        this.buttonTarget.textContent = isSubmitting ? 'Создаем задачу...' : this.defaultButtonText;
    }

    showStatus(message, isError = false) {
        this.statusTarget.textContent = message;
        this.statusTarget.classList.toggle('model-export-status--error', isError);
    }

    clearStatus() {
        this.statusTarget.textContent = '';
        this.statusTarget.classList.remove('model-export-status--error');
    }

    statusLabel(status) {
        return {
            pending: 'ожидает обработки',
            processing: 'в работе',
            completed: 'готово',
            failed: 'ошибка',
        }[status] ?? status;
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        if (errorCode === 'financial_model_already_archived') {
            return 'Архивную модель нельзя выгрузить в Excel.';
        }

        return 'Не удалось создать задачу Excel export.';
    }
}
