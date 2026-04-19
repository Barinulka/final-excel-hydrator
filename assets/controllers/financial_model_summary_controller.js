import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'container',
        'error',
        'investmentStartDate',
        'investmentEndDate',
        'commercialOperationStartDate',
        'commercialOperationEndDate',
        'periodCount',
        'forecastStep',
        'warnings',
    ];

    static values = {
        apiUrl: String,
    };

    connect() {
        this.refresh();
    }

    async refresh() {
        this.clearError();
        this.setLoading(true);

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'GET',
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

            this.applySummary(data.data ?? {});
        } catch (error) {
            this.showError('Ошибка сети. Не удалось загрузить расчетную сводку.');
        } finally {
            this.setLoading(false);
        }
    }

    async readJson(response) {
        try {
            return await response.json();
        } catch (error) {
            return {};
        }
    }

    applySummary(data) {
        const timeParams = data.timeParams ?? {};
        const timeline = data.timeline ?? {};

        this.investmentStartDateTarget.textContent = this.formatDate(timeline.investmentStartDate);
        this.investmentEndDateTarget.textContent = this.formatDate(timeline.investmentEndDate);
        this.commercialOperationStartDateTarget.textContent = this.formatDate(timeline.commercialOperationStartDate);
        this.commercialOperationEndDateTarget.textContent = this.formatDate(timeline.commercialOperationEndDate);
        this.periodCountTarget.textContent = this.formatMonths(timeline.periodCount);
        this.forecastStepTarget.textContent = timeParams.forecastStepLabel || '—';
        this.applyWarnings(data.warnings ?? []);
    }

    applyWarnings(warnings) {
        if (!Array.isArray(warnings) || warnings.length === 0) {
            this.warningsTarget.hidden = true;
            this.warningsTarget.textContent = '';
            return;
        }

        this.warningsTarget.hidden = false;
        this.warningsTarget.textContent = warnings.join(' ');
    }

    formatDate(value) {
        if (typeof value !== 'string') {
            return '—';
        }

        const match = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!match) {
            return '—';
        }

        return `${match[3]}.${match[2]}.${match[1]}`;
    }

    formatMonths(value) {
        if (!Number.isInteger(value)) {
            return '—';
        }

        return `${value} мес.`;
    }

    setLoading(isLoading) {
        this.containerTarget.classList.toggle('model-workspace__summary--loading', isLoading);
        this.containerTarget.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    }

    showError(message) {
        this.errorTarget.textContent = message;
        this.errorTarget.classList.add('project-form__client-error--visible');
    }

    clearError() {
        this.errorTarget.textContent = '';
        this.errorTarget.classList.remove('project-form__client-error--visible');
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        return 'Не удалось загрузить расчетную сводку.';
    }
}
