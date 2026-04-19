import FinancialModelConfirmActionController from '../utils/financial_model_confirm_action_controller.js';

export default class extends FinancialModelConfirmActionController {
    get successMessage() {
        return 'Финансовая модель отправлена в архив.';
    }

    get submittingText() {
        return 'Архивируем...';
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'financial_model_already_archived') {
            return 'Финансовая модель уже находится в архиве.';
        }

        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        return 'Не удалось архивировать финансовую модель.';
    }
}
