import FinancialModelConfirmActionController from '../utils/financial_model_confirm_action_controller.js';

export default class extends FinancialModelConfirmActionController {
    get httpMethod() {
        return 'DELETE';
    }

    get successMessage() {
        return 'Финансовая модель удалена.';
    }

    get submittingText() {
        return 'Удаляем...';
    }

    errorMessageFor(errorCode) {
        if (errorCode === 'active_financial_model_cannot_be_deleted') {
            return 'Сначала отправьте финансовую модель в архив.';
        }

        if (errorCode === 'not_found') {
            return 'Финансовая модель не найдена.';
        }

        return 'Не удалось удалить финансовую модель.';
    }
}
