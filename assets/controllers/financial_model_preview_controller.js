import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'error',
        'loading',
        'refreshButton',
        'tables',
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
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await this.readJson(response);

            if (!response.ok) {
                this.showError(this.errorMessageFor(payload.error));
                return;
            }

            this.renderPreview(payload.data ?? {});
        } catch (error) {
            this.showError('Ошибка сети. Не удалось загрузить preview расчетов.');
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

    renderPreview(data) {
        this.renderWarnings(data.warnings ?? []);
        this.renderTables(data.tables ?? []);
    }

    renderWarnings(warnings) {
        if (!Array.isArray(warnings) || warnings.length === 0) {
            this.warningsTarget.hidden = true;
            this.warningsTarget.textContent = '';
            return;
        }

        this.warningsTarget.hidden = false;
        this.warningsTarget.textContent = warnings.join(' ');
    }

    renderTables(tables) {
        this.tablesTarget.replaceChildren();

        if (!Array.isArray(tables) || tables.length === 0) {
            const emptyState = document.createElement('p');
            emptyState.classList.add('model-preview__empty');
            emptyState.textContent = 'Расчетные таблицы пока не сформированы.';
            this.tablesTarget.append(emptyState);
            return;
        }

        tables.forEach((table) => {
            this.tablesTarget.append(this.createTableBlock(table));
        });
    }

    createTableBlock(tableData) {
        const block = document.createElement('section');
        const title = document.createElement('h3');
        const scroll = document.createElement('div');
        const table = document.createElement('table');
        const thead = document.createElement('thead');
        const tbody = document.createElement('tbody');

        block.classList.add('model-preview__table-block');
        title.classList.add('model-preview__table-title');
        title.textContent = tableData.title || tableData.code || 'Расчетная таблица';

        scroll.classList.add('model-preview__table-scroll');
        table.classList.add('model-preview__table');

        thead.append(this.createHeaderRow(tableData.periods ?? []));
        this.createBodyRows(tableData.rows ?? [], tableData.periods ?? []).forEach((row) => tbody.append(row));

        table.append(thead, tbody);
        scroll.append(table);
        block.append(title, scroll);

        return block;
    }

    createHeaderRow(periods) {
        const row = document.createElement('tr');
        const metricHeader = document.createElement('th');

        metricHeader.scope = 'col';
        metricHeader.textContent = 'Показатель';
        row.append(metricHeader);

        periods.forEach((period, index) => {
            const cell = document.createElement('th');

            cell.scope = 'col';
            cell.textContent = String(index + 1);
            row.append(cell);
        });

        return row;
    }

    createBodyRows(rows, periods) {
        if (!Array.isArray(rows) || rows.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');

            cell.colSpan = periods.length + 1;
            cell.textContent = 'Данные для таблицы пока не сформированы.';
            row.append(cell);

            return [row];
        }

        return rows.map((rowData) => {
            const row = document.createElement('tr');
            const title = document.createElement('th');
            const values = Array.isArray(rowData.values) ? rowData.values : [];

            title.scope = 'row';
            title.textContent = rowData.title || rowData.code || 'Показатель';
            row.append(title);

            periods.forEach((period, index) => {
                row.append(this.createValueCell(values[index], rowData.code));
            });

            return row;
        });
    }

    createValueCell(value, rowCode) {
        const cell = document.createElement('td');

        if (this.isFlagRow(rowCode)) {
            const flag = document.createElement('span');
            const isActive = value === 1 || value === true;

            flag.classList.add('model-timeline__flag');
            flag.classList.toggle('model-timeline__flag--active', isActive);
            flag.textContent = isActive ? '1' : '0';
            cell.append(flag);

            return cell;
        }

        cell.textContent = this.formatValue(value);

        return cell;
    }

    isFlagRow(rowCode) {
        return [
            'investment_activity',
            'operating_activity',
            'operating_start',
        ].includes(rowCode);
    }

    formatValue(value) {
        if (value === undefined || value === null || value === '') {
            return '—';
        }

        if (typeof value === 'string') {
            const dateMatch = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (dateMatch) {
                return `${dateMatch[3]}.${dateMatch[2]}.${dateMatch[1]}`;
            }

            return value;
        }

        if (typeof value === 'number') {
            return value.toLocaleString('ru-RU', { maximumFractionDigits: 2 });
        }

        return String(value);
    }

    setLoading(isLoading) {
        this.loadingTarget.hidden = !isLoading;
        this.refreshButtonTarget.disabled = isLoading;
        this.refreshButtonTarget.textContent = isLoading ? 'Обновляем...' : 'Обновить';
        this.element.classList.toggle('model-preview--loading', isLoading);
    }

    showError(message) {
        this.errorTarget.textContent = message;
        this.errorTarget.classList.add('project-form__client-error--visible');
        this.renderWarnings([]);
        this.renderTables([]);
    }

    clearError() {
        this.errorTarget.textContent = '';
        this.errorTarget.classList.remove('project-form__client-error--visible');
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        return 'Не удалось загрузить preview расчетов.';
    }
}
