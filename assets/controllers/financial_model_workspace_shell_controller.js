import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'handle',
        'navItem',
        'frame',
        'summaryCard',
        'prevStep',
        'nextStep',
        'stepTrigger',
        'stepMenu',
        'stepMenuItem',
    ];

    connect() {
        this.timerA = null;
        this.timerB = null;
        this.closeOnOutsideClick = this.closeOnOutsideClick.bind(this);
        this.closeOnEscape = this.closeOnEscape.bind(this);
        this.closeOnResize = this.closeOnResize.bind(this);

        this.element.classList.add('sidebar-content-visible');
        this.updateSidebarLabels();
        this.syncActiveFromUrl();

        document.addEventListener('click', this.closeOnOutsideClick);
        document.addEventListener('keydown', this.closeOnEscape);
        window.addEventListener('resize', this.closeOnResize);
    }

    disconnect() {
        window.clearTimeout(this.timerA);
        window.clearTimeout(this.timerB);
        document.removeEventListener('click', this.closeOnOutsideClick);
        document.removeEventListener('keydown', this.closeOnEscape);
        window.removeEventListener('resize', this.closeOnResize);
    }

    toggleSidebar(event) {
        event.preventDefault();
        event.stopPropagation();

        window.clearTimeout(this.timerA);
        window.clearTimeout(this.timerB);
        this.closeStepMenu();

        const collapsed = this.element.classList.contains('sidebar-collapsed');

        if (collapsed) {
            this.element.classList.remove('sidebar-collapsed', 'sidebar-closing', 'sidebar-content-visible');
            this.element.classList.add('sidebar-opening');
            this.updateSidebarLabels();

            this.timerA = window.setTimeout(() => {
                this.element.classList.remove('sidebar-opening');
                this.element.classList.add('sidebar-content-visible');
                this.updateStepNavigationState();
            }, 260);

            return;
        }

        this.element.classList.remove('sidebar-opening', 'sidebar-content-visible');
        this.element.classList.add('sidebar-closing');

        this.timerA = window.setTimeout(() => {
            this.element.classList.add('sidebar-collapsed');
            this.updateSidebarLabels();
        }, 110);

        this.timerB = window.setTimeout(() => {
            this.element.classList.remove('sidebar-closing');
            this.updateStepNavigationState();
        }, 420);
    }

    toggleGroup(event) {
        event.currentTarget.classList.toggle('expanded');
    }

    activateNavItem(event) {
        const clickedLink = event.currentTarget;

        if (this.hasSummaryCardTarget) {
            this.summaryCardTarget.classList.remove('active');
        }

        this.navItemTargets.forEach((link) => {
            link.classList.toggle('active', link === clickedLink);
        });

        this.closeStepMenu();
    }

    activateSummaryCard() {
        if (this.hasSummaryCardTarget) {
            this.summaryCardTarget.classList.add('active');
        }

        this.navItemTargets.forEach((link) => {
            link.classList.remove('active');
        });

        this.closeStepMenu();
    }

    activateStepMenuItem(event) {
        const clickedLink = event.currentTarget;

        if (this.hasSummaryCardTarget && this.samePath(this.summaryCardTarget.href, clickedLink.href)) {
            this.summaryCardTarget.classList.add('active');
            this.navItemTargets.forEach((link) => {
                link.classList.remove('active');
            });
            return;
        }

        if (this.hasSummaryCardTarget) {
            this.summaryCardTarget.classList.remove('active');
        }

        this.navItemTargets.forEach((link) => {
            link.classList.toggle('active', this.samePath(link.href, clickedLink.href));
        });
    }

    syncActiveFromUrl() {
        const currentPath = window.location.pathname;
        const isDashboard = currentPath.endsWith('/dashboard');

        if (this.hasSummaryCardTarget) {
            this.summaryCardTarget.classList.toggle('active', isDashboard);
        }

        this.navItemTargets.forEach((link) => {
            const linkPath = this.pathFromHref(link.href);
            link.classList.toggle('active', !isDashboard && linkPath === currentPath);
        });

        this.syncStepMenuFromUrl();
        this.updateStepNavigationState();
    }

    toggleStepMenu(event) {
        event.preventDefault();
        event.stopPropagation();

        if (!this.hasStepMenuTarget || !this.hasStepTriggerTarget) {
            return;
        }

        if (!this.isCompactNavigationMode()) {
            return;
        }

        const open = this.stepMenuTarget.classList.toggle('open');
        this.stepTriggerTarget.setAttribute('aria-expanded', open ? 'true' : 'false');
        this.syncStepMenuFromUrl();
    }

    closeStepMenu() {
        if (this.hasStepMenuTarget) {
            this.stepMenuTarget.classList.remove('open');
        }

        if (this.hasStepTriggerTarget) {
            this.stepTriggerTarget.setAttribute('aria-expanded', 'false');
        }
    }

    navigatePrevious(event) {
        event.preventDefault();
        this.navigateByOffset(-1);
    }

    navigateNext(event) {
        event.preventDefault();
        this.navigateByOffset(1);
    }

    navigateByOffset(offset) {
        const items = this.navigationItems();
        const currentIndex = this.currentNavigationIndex(items);

        if (currentIndex === -1) {
            return;
        }

        const nextItem = items[currentIndex + offset];

        if (!nextItem) {
            return;
        }

        nextItem.click();
    }

    updateSidebarLabels() {
        if (!this.hasHandleTarget) {
            return;
        }

        const collapsed = this.element.classList.contains('sidebar-collapsed');
        this.handleTarget.setAttribute('aria-label', collapsed ? 'Раскрыть меню' : 'Скрыть меню');
        this.handleTarget.setAttribute('title', collapsed ? 'Раскрыть меню' : 'Скрыть меню');
    }

    updateStepNavigationState() {
        const items = this.navigationItems();
        const currentIndex = this.currentNavigationIndex(items);

        if (this.hasPrevStepTarget) {
            this.prevStepTarget.disabled = currentIndex <= 0;
        }

        if (this.hasNextStepTarget) {
            this.nextStepTarget.disabled = currentIndex === -1 || currentIndex >= items.length - 1;
        }
    }

    closeOnOutsideClick(event) {
        if (!this.hasStepMenuTarget || !this.hasStepTriggerTarget) {
            return;
        }

        if (this.stepMenuTarget.contains(event.target) || this.stepTriggerTarget.contains(event.target)) {
            return;
        }

        this.closeStepMenu();
    }

    closeOnEscape(event) {
        if (event.key === 'Escape') {
            this.closeStepMenu();
        }
    }

    closeOnResize() {
        if (!this.isCompactNavigationMode()) {
            this.closeStepMenu();
        }
    }

    isCompactNavigationMode() {
        return this.element.classList.contains('sidebar-collapsed')
            || window.matchMedia('(max-width: 1180px)').matches;
    }

    syncStepMenuFromUrl() {
        if (!this.hasStepMenuItemTarget) {
            return;
        }

        const currentPath = window.location.pathname;
        let activeLabel = null;

        this.stepMenuItemTargets.forEach((link) => {
            const active = this.pathFromHref(link.href) === currentPath;
            link.classList.toggle('active', active);

            if (active) {
                activeLabel = this.formatStepTriggerLabel(link);
            }
        });

        if (activeLabel && this.hasStepTriggerTarget) {
            this.stepTriggerTarget.textContent = activeLabel;
        }
    }

    navigationItems() {
        if (this.hasStepMenuItemTarget) {
            return this.stepMenuItemTargets;
        }

        return [
            ...(this.hasSummaryCardTarget ? [this.summaryCardTarget] : []),
            ...this.navItemTargets,
        ];
    }

    currentNavigationIndex(items) {
        const currentPath = window.location.pathname;

        return items.findIndex((item) => this.pathFromHref(item.href) === currentPath);
    }

    samePath(leftHref, rightHref) {
        return this.pathFromHref(leftHref) === this.pathFromHref(rightHref);
    }

    formatStepTriggerLabel(link) {
        const name = link.querySelector('.step-menu-name')?.textContent?.trim() || link.textContent.trim();
        const index = link.querySelector('.step-menu-index')?.textContent?.trim();

        if (!index || index === '—') {
            return name;
        }

        return `Шаг ${Number.parseInt(index, 10)} из ${this.stepCount()} · ${name}`;
    }

    stepCount() {
        return this.stepMenuItemTargets.filter((link) => {
            const index = link.querySelector('.step-menu-index')?.textContent?.trim();

            return index && index !== '—';
        }).length;
    }

    pathFromHref(href) {
        try {
            return new URL(href, window.location.origin).pathname;
        } catch (error) {
            return href;
        }
    }
}
