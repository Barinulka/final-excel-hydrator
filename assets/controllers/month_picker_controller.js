import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'input',
        'trigger',
        'panel',
        'year',
        'months',
    ];

    connect() {
        this.monthLabels = [
            'Январь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь',
        ];

        this.shortMonthLabels = [
            'Янв',
            'Фев',
            'Мар',
            'Апр',
            'Май',
            'Июн',
            'Июл',
            'Авг',
            'Сен',
            'Окт',
            'Ноя',
            'Дек',
        ];

        const selected = this.parseValue(this.inputTarget.value);
        this.viewYear = selected?.year ?? new Date().getFullYear();

        this.boundCloseOnOutsideClick = this.closeOnOutsideClick.bind(this);
        this.boundCloseOnEscape = this.closeOnEscape.bind(this);
        document.addEventListener('click', this.boundCloseOnOutsideClick);
        document.addEventListener('keydown', this.boundCloseOnEscape);

        this.syncFromInput();
        this.render();
        this.close();
    }

    disconnect() {
        document.removeEventListener('click', this.boundCloseOnOutsideClick);
        document.removeEventListener('keydown', this.boundCloseOnEscape);
    }

    toggle(event) {
        event.preventDefault();
        event.stopPropagation();

        if (this.panelTarget.hidden) {
            this.open();
            return;
        }

        this.close();
    }

    open() {
        this.panelTarget.hidden = false;
        this.triggerTarget.setAttribute('aria-expanded', 'true');
        this.render();
    }

    close() {
        this.panelTarget.hidden = true;
        this.triggerTarget.setAttribute('aria-expanded', 'false');
    }

    previousYear() {
        this.viewYear -= 1;
        this.render();
    }

    nextYear() {
        this.viewYear += 1;
        this.render();
    }

    selectMonth(event) {
        const month = event.currentTarget.dataset.month;
        this.inputTarget.value = `${this.viewYear}-${month}`;
        this.inputTarget.dispatchEvent(new Event('input', { bubbles: true }));
        this.inputTarget.dispatchEvent(new Event('change', { bubbles: true }));
        this.syncFromInput();
        this.close();
    }

    syncFromInput() {
        const selected = this.parseValue(this.inputTarget.value);

        if (selected) {
            this.viewYear = selected.year;
            this.triggerTarget.textContent = `${this.monthLabels[selected.month - 1]} ${selected.year}`;
            this.triggerTarget.classList.add('month-picker__trigger--filled');
        } else {
            this.triggerTarget.textContent = 'Выберите месяц';
            this.triggerTarget.classList.remove('month-picker__trigger--filled');
        }

        this.render();
    }

    render() {
        this.yearTarget.textContent = this.viewYear;
        this.monthsTarget.replaceChildren();

        const selected = this.parseValue(this.inputTarget.value);

        for (let index = 0; index < 12; index += 1) {
            const month = String(index + 1).padStart(2, '0');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'month-picker__month';
            button.dataset.month = month;
            button.dataset.action = 'month-picker#selectMonth';
            button.textContent = this.shortMonthLabels[index];

            if (selected?.year === this.viewYear && selected?.month === index + 1) {
                button.classList.add('month-picker__month--selected');
            }

            this.monthsTarget.append(button);
        }
    }

    closeOnOutsideClick(event) {
        if (this.element.contains(event.target)) {
            return;
        }

        this.close();
    }

    closeOnEscape(event) {
        if (event.key !== 'Escape') {
            return;
        }

        this.close();
    }

    parseValue(value) {
        const match = /^(\d{4})-(0[1-9]|1[0-2])$/.exec(value);

        if (!match) {
            return null;
        }

        return {
            year: Number(match[1]),
            month: Number(match[2]),
        };
    }
}
