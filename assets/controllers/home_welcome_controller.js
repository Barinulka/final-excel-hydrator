import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['overlay'];

    open() {
        this.overlayTarget.hidden = false;
        document.body.classList.add('home-welcome-open');
    }

    close() {
        if (!this.hasOverlayTarget) {
            return;
        }

        this.overlayTarget.hidden = true;
        document.body.classList.remove('home-welcome-open');
    }

    disconnect() {
        document.body.classList.remove('home-welcome-open');
    }
}
