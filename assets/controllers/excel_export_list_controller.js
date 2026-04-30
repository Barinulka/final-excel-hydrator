import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'body',
        'empty',
        'error',
        'loading',
        'refreshButton',
        'table',
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

            const payload = await this.readJson(response);

            if (!response.ok) {
                this.showError(this.errorMessageFor(payload.error));
                return;
            }

            this.renderExports(payload.data?.exports ?? []);
        } catch (error) {
            this.showError('Ошибка сети. Не удалось загрузить задачи export.');
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

    renderExports(exports) {
        this.bodyTarget.replaceChildren();

        if (!Array.isArray(exports) || exports.length === 0) {
            this.tableTarget.hidden = true;
            this.emptyTarget.hidden = false;
            return;
        }

        this.tableTarget.hidden = false;
        this.emptyTarget.hidden = true;

        exports.forEach((excelExport) => {
            const row = document.createElement('tr');

            row.append(
                this.createStatusCell(excelExport.status),
                this.createTextCell(this.formatDateTime(excelExport.createdAt)),
                this.createTextCell(this.formatDateTime(excelExport.startedAt)),
                this.createTextCell(this.formatDateTime(excelExport.completedAt)),
                this.createTextCell(this.formatDateTime(excelExport.failedAt)),
                this.createTextCell(this.fileOrError(excelExport)),
            );

            this.bodyTarget.append(row);
        });
    }

    createStatusCell(status) {
        const cell = document.createElement('td');
        const badge = document.createElement('span');
        const normalizedStatus = typeof status === 'string' ? status : 'pending';

        badge.classList.add('model-export-list__status', `model-export-list__status--${normalizedStatus}`);
        badge.textContent = this.statusLabel(normalizedStatus);
        cell.append(badge);

        return cell;
    }

    createTextCell(value) {
        const cell = document.createElement('td');

        cell.textContent = value === undefined || value === null || value === '' ? '—' : String(value);

        return cell;
    }

    fileOrError(excelExport) {
        if (excelExport.status === 'failed') {
            return excelExport.errorMessage || 'Ошибка без описания';
        }

        return excelExport.filePath || '—';
    }

    formatDateTime(value) {
        if (typeof value !== 'string' || value === '') {
            return '—';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return '—';
        }

        return new Intl.DateTimeFormat('ru-RU', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    setLoading(isLoading) {
        this.element.classList.toggle('model-export-list--loading', isLoading);
        this.element.setAttribute('aria-busy', isLoading ? 'true' : 'false');
        this.loadingTarget.hidden = !isLoading;
        this.refreshButtonTarget.disabled = isLoading;
    }

    showError(message) {
        this.errorTarget.textContent = message;
        this.errorTarget.classList.add('project-form__client-error--visible');
        this.tableTarget.hidden = true;
        this.emptyTarget.hidden = true;
    }

    clearError() {
        this.errorTarget.textContent = '';
        this.errorTarget.classList.remove('project-form__client-error--visible');
    }

    statusLabel(status) {
        return {
            pending: 'Ожидает',
            processing: 'В работе',
            completed: 'Готово',
            failed: 'Ошибка',
        }[status] ?? status;
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        return 'Не удалось загрузить задачи export.';
    }
}
