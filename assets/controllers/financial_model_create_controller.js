import { Controller } from '@hotwired/stimulus';
import { storePendingToast } from '../utils/toast.js';

export default class extends Controller {
    static targets = [
        'dialog',
        'investmentStartMonth',
        'investmentStartMonthTrigger',
        'investmentDurationMonths',
        'commercialOperationDurationMonths',
        'forecastStep',
        'investmentStartMonthError',
        'investmentDurationMonthsError',
        'commercialOperationDurationMonthsError',
        'forecastStepError',
        'formError',
        'submit',
    ];

    static values = {
        apiUrl: String,
    };

    open() {
        this.clearAllErrors();
        this.dialogTarget.showModal();
        this.investmentStartMonthTriggerTarget.focus();
    }

    close() {
        this.dialogTarget.close();
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
            this.close();
        }
    }

    async submit(event) {
        event.preventDefault();
        this.clearAllErrors();
        this.setSubmitting(true);

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(this.buildPayload()),
            });

            const data = await this.readJson(response);

            if (response.status === 422) {
                this.applyValidationErrors(data.fields ?? {});
                return;
            }

            if (!response.ok) {
                this.showError(this.formErrorTarget, 'Не удалось создать финансовую модель.');
                return;
            }

            const redirectUrl = data.data?.redirectUrl;
            if (!redirectUrl) {
                this.showError(this.formErrorTarget, 'Сервер вернул некорректный ответ.');
                return;
            }

            storePendingToast('Финансовая модель создана.');
            window.location.href = redirectUrl;
        } catch (error) {
            this.showError(this.formErrorTarget, 'Ошибка сети. Попробуйте еще раз.');
        } finally {
            this.setSubmitting(false);
        }
    }

    buildPayload() {
        return {
            investmentStartMonth: this.investmentStartMonthTarget.value,
            investmentDurationMonths: this.investmentDurationMonthsTarget.value,
            commercialOperationDurationMonths: this.commercialOperationDurationMonthsTarget.value,
            forecastStep: this.forecastStepTarget.value,
        };
    }

    async readJson(response) {
        try {
            return await response.json();
        } catch (error) {
            return {};
        }
    }

    applyValidationErrors(fields) {
        if (fields.investmentStartMonth?.length) {
            this.showError(this.investmentStartMonthErrorTarget, fields.investmentStartMonth[0]);
        }

        if (fields.investmentDurationMonths?.length) {
            this.showError(this.investmentDurationMonthsErrorTarget, fields.investmentDurationMonths[0]);
        }

        if (fields.commercialOperationDurationMonths?.length) {
            this.showError(this.commercialOperationDurationMonthsErrorTarget, fields.commercialOperationDurationMonths[0]);
        }

        if (fields.forecastStep?.length) {
            this.showError(this.forecastStepErrorTarget, fields.forecastStep[0]);
        }
    }

    clearAllErrors() {
        this.clearError(this.investmentStartMonthErrorTarget);
        this.clearError(this.investmentDurationMonthsErrorTarget);
        this.clearError(this.commercialOperationDurationMonthsErrorTarget);
        this.clearError(this.forecastStepErrorTarget);
        this.clearError(this.formErrorTarget);
    }

    showError(target, message) {
        target.textContent = message;
        target.classList.add('project-form__error--visible');
    }

    clearError(target) {
        target.textContent = '';
        target.classList.remove('project-form__error--visible');
    }

    setSubmitting(isSubmitting) {
        this.submitTarget.disabled = isSubmitting;
        this.submitTarget.textContent = isSubmitting ? 'Создаем...' : 'Создать модель';
    }
}
