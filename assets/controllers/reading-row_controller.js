import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {

    }

    toggle() {
        this.element.querySelectorAll("[data-name='indicator']")[0].classList.toggle("rotate-180");
        var wrapper = this.element.nextElementSibling.querySelectorAll('div')[0]
        if ('0px' === wrapper.style.height) {
            wrapper.style.height = (wrapper.scrollHeight + 5) + 'px';
        } else {
            wrapper.style.height = '0px';
        }
    }
}
