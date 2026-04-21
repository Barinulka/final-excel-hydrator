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
        'periodsHead',
        'periodsBody',
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
        this.renderPeriods(timeline.periods ?? []);
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

    renderPeriods(periods) {
        if (!this.hasPeriodsHeadTarget || !this.hasPeriodsBodyTarget) {
            return;
        }

        this.periodsHeadTarget.replaceChildren();
        this.periodsBodyTarget.replaceChildren();

        if (!Array.isArray(periods) || periods.length === 0) {
            this.renderPeriodsHeader([]);

            const row = document.createElement('tr');
            const cell = document.createElement('td');

            cell.colSpan = 3;
            cell.textContent = 'Временной ряд пока не сформирован.';
            row.append(cell);
            this.periodsBodyTarget.append(row);

            return;
        }

        this.renderPeriodsHeader(periods);

        [
            {
                label: 'Начало месяца',
                unit: 'дата',
                value: (period) => this.formatDate(period.periodStartDate),
            },
            {
                label: 'Окончание месяца',
                unit: 'дата',
                value: (period) => this.formatDate(period.periodEndDate),
            },
            {
                label: 'Инвестиционная деятельность',
                unit: 'флаг',
                value: (period) => period.investmentActivity,
                isFlag: true,
            },
            {
                label: 'Операционная деятельность',
                unit: 'флаг',
                value: (period) => period.operatingActivity,
                isFlag: true,
            },
            {
                label: 'Начало операционной деятельности',
                unit: 'флаг',
                value: (period) => period.operatingStart,
                isFlag: true,
            },
        ].forEach((rowConfig) => {
            const row = document.createElement('tr');
            const labelCell = document.createElement('th');

            labelCell.scope = 'row';
            labelCell.textContent = rowConfig.label;
            row.append(labelCell, this.createTextCell(rowConfig.unit, 'model-timeline__unit'));

            periods.forEach((period) => {
                row.append(rowConfig.isFlag
                    ? this.createFlagCell(rowConfig.value(period))
                    : this.createTextCell(rowConfig.value(period))
                );
            });

            this.periodsBodyTarget.append(row);
        });
    }

    renderPeriodsHeader(periods) {
        const row = document.createElement('tr');

        row.append(
            this.createHeaderCell('Показатель'),
            this.createHeaderCell('Ед.'),
        );

        if (!Array.isArray(periods) || periods.length === 0) {
            row.append(this.createHeaderCell('Периоды'));
            this.periodsHeadTarget.append(row);

            return;
        }

        periods.forEach((period) => {
            row.append(this.createHeaderCell(this.formatYearMonth(period.yearMonth)));
        });

        this.periodsHeadTarget.append(row);
    }

    createHeaderCell(value) {
        const cell = document.createElement('th');

        cell.scope = 'col';
        cell.textContent = value;

        return cell;
    }

    createTextCell(value, className = null) {
        const cell = document.createElement('td');
        cell.textContent = value === undefined || value === null || value === '' ? '—' : String(value);

        if (className) {
            cell.classList.add(className);
        }

        return cell;
    }

    createFlagCell(value) {
        const cell = document.createElement('td');
        const flag = document.createElement('span');
        const isActive = value === true;

        flag.classList.add('model-timeline__flag');
        flag.classList.toggle('model-timeline__flag--active', isActive);
        flag.textContent = isActive ? '1' : '0';
        cell.append(flag);

        return cell;
    }

    formatYearMonth(value) {
        if (typeof value !== 'string') {
            return '—';
        }

        const match = value.match(/^(\d{4})-(\d{2})$/);
        if (!match) {
            return '—';
        }

        return `${match[2]}.${match[1]}`;
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
        this.renderPeriods([]);
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
