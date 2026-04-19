import FinancialModelConfirmActionController from '../utils/financial_model_confirm_action_controller.js';

export default class extends FinancialModelConfirmActionController {
    get successMessage() {
        return 'Финансовая модель восстановлена.';
    }

    get submittingText() {
        return 'Восстанавливаем...';
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'financial_model_already_active') {
            return 'Финансовая модель уже активна.';
        }

        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        return 'Не удалось восстановить финансовую модель.';
    }
}
