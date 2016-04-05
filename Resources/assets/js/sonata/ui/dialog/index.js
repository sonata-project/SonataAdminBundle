import $ from 'jquery';

import raf from 'sonata/util/raf';
import rafPromise from 'sonata/util/raf/promise';
import dedupeScripts from 'sonata/util/dedupeScripts';
import {createSpinner} from 'sonata/ui/spinner';
import SKELETON from './dialog.html';
import './dialog.css';


export default class Dialog
{
    constructor (title) {
        const id = Date.now();
        this.$root = $(SKELETON)
            .attr('id', `dialog_${id}`)
            .attr('aria-labelledby', `dialog_title_${id}`)
        ;
        this.$title = this.$root.find('.modal-title')
            .attr('id', `dialog_title_${id}`)
            .text(title)
        ;
        this.$body = this.$root.find('.modal-body-content');
        this.$spinner = this.$root.find('.overlay')
            .append(createSpinner(64))
        ;
    }

    /**
     * @property {jQuery} The dialog's body.
     */
    get body () {
        return this.$body;
    }

    appendTo (element) {
        this.$root.appendTo(element);

        return this;
    }

    /**
     * Sets the contents of the dialog's body.
     *
     * @param {string} content
     * @returns {Promise.<Dialog>}
     * @fires sonata:domready
     */
    setContent (content) {
        return rafPromise(() => {
            this.$body.html(dedupeScripts(content));
            this.$body.trigger('sonata:domready');
        }).then(() => this.hideSpinner());
    }

    /**
     * Open the dialog.
     * Will be removed automatically from the DOM when closed.
     *
     * @returns {Promise.<Dialog>}
     */
    open () {
        return new Promise(resolve => {
            this.$root
                .one('shown.bs.modal', () => resolve(this))
                .one('hidden.bs.modal', () => this.$root.remove())
            ;
            this.$spinner.css('display', '');
            raf(() => this.$root.modal());
        });
    }

    /**
     * Close the dialog and remove it from the DOM.
     *
     * @returns {Promise.<Dialog>}
     */
    close () {
        return new Promise(resolve => {
            this.$root.one('hidden.bs.modal', () => {
                this.$root.remove();
                resolve(this);
            });
            raf(() => this.$root.modal('hide'));
        });
    }

    /**
     * Shows the spinner overlay.
     *
     * @returns {Promise.<Dialog>}
     */
    showSpinner () {
        return rafPromise(() => this.$spinner.fadeIn(100).promise())
            .then(() => this);
    }

    /**
     * Hides the spinner overlay.
     *
     * @returns {Promise.<Dialog>}
     */
    hideSpinner () {
        return rafPromise(() => this.$spinner.fadeOut(100).promise())
            .then(() => this);
    }
}
