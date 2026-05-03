import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['handle', 'navItem', 'frame'];

    connect() {
        this.timerA = null;
        this.timerB = null;

        this.element.classList.add('sidebar-content-visible');
        this.updateSidebarLabels();
        this.syncActiveFromUrl();
    }

    disconnect() {
        window.clearTimeout(this.timerA);
        window.clearTimeout(this.timerB);
    }

    toggleSidebar(event) {
        event.preventDefault();
        event.stopPropagation();

        window.clearTimeout(this.timerA);
        window.clearTimeout(this.timerB);

        const collapsed = this.element.classList.contains('sidebar-collapsed');

        if (collapsed) {
            this.element.classList.remove('sidebar-collapsed', 'sidebar-closing', 'sidebar-content-visible');
            this.element.classList.add('sidebar-opening');
            this.updateSidebarLabels();

            this.timerA = window.setTimeout(() => {
                this.element.classList.remove('sidebar-opening');
                this.element.classList.add('sidebar-content-visible');
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
        }, 420);
    }

    toggleGroup(event) {
        event.currentTarget.classList.toggle('expanded');
    }

    activateNavItem(event) {
        const clickedLink = event.currentTarget;

        this.navItemTargets.forEach((link) => {
            link.classList.toggle('active', link === clickedLink);
        });
    }

    syncActiveFromUrl() {
        if (!this.hasNavItemTarget) {
            return;
        }

        const currentPath = window.location.pathname;

        this.navItemTargets.forEach((link) => {
            const linkPath = this.pathFromHref(link.href);
            link.classList.toggle('active', linkPath === currentPath);
        });
    }

    updateSidebarLabels() {
        if (!this.hasHandleTarget) {
            return;
        }

        const collapsed = this.element.classList.contains('sidebar-collapsed');
        this.handleTarget.setAttribute('aria-label', collapsed ? 'Раскрыть меню' : 'Скрыть меню');
        this.handleTarget.setAttribute('title', collapsed ? 'Раскрыть меню' : 'Скрыть меню');
    }

    pathFromHref(href) {
        try {
            return new URL(href, window.location.origin).pathname;
        } catch (error) {
            return href;
        }
    }
}
