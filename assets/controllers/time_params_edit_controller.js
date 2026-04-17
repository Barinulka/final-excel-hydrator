import { Controller } from '@hotwired/stimulus';
import { showToast } from '../utils/toast.js';

export default class extends Controller {
    static targets = [
        'investmentStartMonth',
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

    async submit(event) {
        event.preventDefault();
        this.clearAllMessages();
        this.setSubmitting(true);

        try {
            const response = await fetch(this.apiUrlValue, {
                method: 'PUT',
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
                this.showError(this.formErrorTarget, 'Не удалось сохранить временные параметры.');
                return;
            }

            this.applySavedValues(data.data ?? {});
            showToast('Временные параметры сохранены.');
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

    applySavedValues(data) {
        if (data.investmentStartMonth) {
            this.investmentStartMonthTarget.value = data.investmentStartMonth;
            this.investmentStartMonthTarget.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (data.investmentDurationMonths) {
            this.investmentDurationMonthsTarget.value = data.investmentDurationMonths;
        }

        if (data.commercialOperationDurationMonths) {
            this.commercialOperationDurationMonthsTarget.value = data.commercialOperationDurationMonths;
        }

        if (data.forecastStep) {
            this.forecastStepTarget.value = data.forecastStep;
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

    clearAllMessages() {
        this.clearError(this.investmentStartMonthErrorTarget);
        this.clearError(this.investmentDurationMonthsErrorTarget);
        this.clearError(this.commercialOperationDurationMonthsErrorTarget);
        this.clearError(this.forecastStepErrorTarget);
        this.clearError(this.formErrorTarget);
    }

    showError(target, message) {
        target.textContent = message;
        target.classList.add('project-form__client-error--visible');
    }

    clearError(target) {
        target.textContent = '';
        target.classList.remove('project-form__client-error--visible');
    }

    setSubmitting(isSubmitting) {
        this.submitTarget.disabled = isSubmitting;
        this.submitTarget.textContent = isSubmitting ? 'Сохраняем...' : 'Сохранить';
    }
}
