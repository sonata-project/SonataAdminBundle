/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { Controller } from '@hotwired/stimulus';

const EVENT_NAMESPACE = '.sonataModelList';

/**
 * Handles the ModelListType widget: the list, add, edit and delete buttons,
 * the modal used by them and the short object description of the selected object.
 */
export default class extends Controller {
  static targets = ['input', 'description', 'editButton', 'dialog'];

  static values = {
    label: String,
    shortObjectDescriptionUrl: String,
    editUrl: String,
    placeholder: String,
    loadingInformation: String,
    loaderImage: String,
  };

  connect() {
    this.onInputChange = this.onInputChange.bind(this);
    this.onListLinkClick = this.onListLinkClick.bind(this);
    this.onListFormSubmit = this.onListFormSubmit.bind(this);
    this.onFormAction = this.onFormAction.bind(this);

    // Bound through jQuery, so changes triggered with jQuery(...).trigger('change') are handled too.
    jQuery(this.inputTarget).on(`change${EVENT_NAMESPACE}`, this.onInputChange);
  }

  disconnect() {
    jQuery(this.inputTarget).off(EVENT_NAMESPACE);

    if (this.dialog) {
      // Put the dialog back, so it is removed together with the widget and found again on reconnect.
      this.dialog.off(EVENT_NAMESPACE);
      this.element.appendChild(this.dialog.get(0));
      this.dialog = null;
    }
  }

  list(event) {
    event.preventDefault();
    event.stopPropagation();

    this.log('open the list modal');
    this.openDialog(event.currentTarget.getAttribute('href'), 'list');
  }

  add(event) {
    event.preventDefault();
    event.stopPropagation();

    this.log('add link action');
    this.openDialog(event.currentTarget.getAttribute('href'), 'form');
  }

  edit(event) {
    event.preventDefault();
    event.stopPropagation();

    this.log('edit link action');
    this.openDialog(event.currentTarget.getAttribute('href'), 'form');
  }

  remove(event) {
    event.preventDefault();

    const input = jQuery(this.inputTarget);

    if (input.find('option').get(0)) {
      input.attr('selectedIndex', '-1').children('option:selected').attr('selected', false);
    }

    input.val('').trigger('change');
  }

  onInputChange() {
    this.log('update the label');

    const objectId = jQuery(this.inputTarget).val();
    const description = jQuery(this.descriptionTarget);

    // Skip short object information call if object is missing.
    if (objectId === undefined || objectId === null || objectId === '') {
      description.html(
        jQuery('<span class="inner-field-short-description"></span>').text(this.placeholderValue)
      );

      if (this.hasEditButtonTarget) {
        this.editButtonTarget.classList.add('hidden');
      }

      return;
    }

    description.html(
      jQuery('<span></span>')
        .append(
          jQuery('<img alt="" style="vertical-align: middle; margin-right: 10px">').attr(
            'src',
            this.loaderImageValue
          )
        )
        .append(document.createTextNode(this.loadingInformationValue))
    );

    jQuery.ajax({
      type: 'GET',
      url: this.shortObjectDescriptionUrlValue.replace('OBJECT_ID', objectId),
      dataType: 'html',
      success: (html) => {
        description.html(html);
      },
    });

    if (this.hasEditButtonTarget && this.editUrlValue !== '') {
      this.editButtonTarget.classList.remove('hidden');
      this.editButtonTarget.setAttribute('href', this.editUrlValue.replace('OBJECT_ID', objectId));
    }
  }

  /**
   * @param {string} url
   * @param {'list'|'form'} mode
   */
  openDialog(url, mode) {
    const dialog = this.initializeDialog();
    const content = this.getDialogContent();

    content.html('');

    dialog.off(EVENT_NAMESPACE);

    if (mode === 'list') {
      dialog.on(`click${EVENT_NAMESPACE}`, 'a', this.onListLinkClick);
      dialog.on(`submit${EVENT_NAMESPACE}`, 'form', this.onListFormSubmit);
    } else {
      dialog.on(`click${EVENT_NAMESPACE}`, 'a', this.onFormAction);
      dialog.on(`submit${EVENT_NAMESPACE}`, 'form', this.onFormAction);
    }

    jQuery.ajax({
      url,
      dataType: 'html',
      success: (html) => {
        this.log('ajax success, populate the dialog');

        content.html(html);

        if (this.labelValue !== '') {
          dialog.find('.modal-title').first().text(this.labelValue);
        }

        window.Admin.shared_setup(dialog);

        dialog.modal();

        window.Admin.setup_list_modal(dialog);
      },
    });
  }

  /**
   * Handles a link click in the list shown in the dialog:
   * - if the link is inside a row, the related input gets updated
   * - otherwise (filters, pagination, ...) the content of the dialog is refreshed
   */
  onListLinkClick(event) {
    const link = jQuery(event.currentTarget);
    const href = link.attr('href');

    if (href === undefined || href.length === 0 || href === '#') {
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    const dialog = this.initializeDialog();
    const row = link.closest('.sonata-ba-list-field', dialog.get(0));

    // the user does not click on a row column
    if (row.length === 0) {
      this.log('the user does not click on a row column, make ajax call to retrieve inner html');

      jQuery.ajax({
        type: 'GET',
        url: href,
        dataType: 'html',
        success: (html) => {
          this.getDialogContent().html(html);

          window.Admin.shared_setup(dialog);
        },
      });

      return;
    }

    this.log('the user select one element, update input and hide the modal');

    jQuery(this.inputTarget).val(row.attr('objectId')).trigger('change');

    dialog.modal('hide');
  }

  /**
   * Submits forms of the list shown in the dialog (ie: the filters) and refreshes the dialog.
   */
  onListFormSubmit(event) {
    event.preventDefault();

    const form = jQuery(event.currentTarget);

    this.log('catching submit event, sending ajax request');

    form.ajaxSubmit({
      type: form.attr('method'),
      url: form.attr('action'),
      dataType: 'html',
      data: { _xml_http_request: true },
      success: (html) => {
        this.getDialogContent().html(html);

        window.Admin.shared_setup(this.initializeDialog());
      },
    });
  }

  /**
   * Handles links and forms of the add/edit form shown in the dialog.
   */
  onFormAction(event) {
    const element = event.currentTarget;
    const target = jQuery(element);

    // return if the link is an anchor inside the same page
    if (element.nodeName === 'A') {
      const href = target.attr('href');

      if (
        href === undefined ||
        href.length === 0 ||
        href[0] === '#' ||
        href.toLowerCase().startsWith('javascript:') // eslint-disable-line no-script-url
      ) {
        this.log('element is an anchor or a script, skipping action');

        return;
      }
    }

    event.preventDefault();
    event.stopPropagation();

    let url;
    let type;

    if (element.nodeName === 'FORM') {
      url = target.attr('action');
      type = target.attr('method');
    } else if (element.nodeName === 'A') {
      url = target.attr('href');
      type = 'GET';
    } else {
      return;
    }

    if (target.hasClass('sonata-ba-action')) {
      this.log('reserved action stop catch all events');

      return;
    }

    const content = this.getDialogContent();

    // Remove old error messages.
    content.find('div.alert-danger').remove();

    this.log('execute ajax call');

    target.ajaxSubmit({
      url,
      type,
      headers: {
        Accept: 'application/json',
      },
      data: {
        _xml_http_request: true,
      },
      success: (data) => {
        // if the crud action return ok, then the element has been added
        // so the widget input must be updated with the new object
        if (typeof data !== 'string' && data.result === 'ok') {
          this.initializeDialog().modal('hide');

          jQuery(this.inputTarget).val(data.objectId).trigger('change');

          return;
        }

        // otherwise, display form error
        content.html(data);

        window.Admin.shared_setup(this.initializeDialog());
      },
      error: (xhr) => {
        content.prepend(this.renderErrors(xhr, element));

        // Reset the submit buttons.
        target.find('button[type="submit"]').removeAttr('disabled');
      },
    });
  }

  /**
   * @param {XMLHttpRequest} xhr
   * @param {HTMLElement} element
   * @returns {string}
   */
  renderErrors(xhr, element) {
    if (xhr.getResponseHeader('Content-Type') !== 'application/json') {
      return xhr.responseText;
    }

    const alert = (message) =>
      '<div class="alert alert-danger alert-dismissable">' +
      '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
      `${message}</div>`;

    const jsonContent = JSON.parse(xhr.responseText);
    let content = jsonContent.title ? alert(jsonContent.title) : '';

    if (!jsonContent.violations) {
      return content;
    }

    element.querySelectorAll('.help-block.sonata-ba-field-error-messages').forEach((el) => {
      el.remove();
    });

    element.querySelectorAll('.has-error').forEach((el) => {
      el.classList.remove('has-error');
    });

    element.querySelectorAll('button').forEach((el) => {
      el.removeAttribute('disabled');
    });

    jsonContent.violations.forEach((violation) => {
      const field = element.querySelector(`[name="${violation.propertyPath}"]`);

      if (!field) {
        content += alert(violation.title);

        return;
      }

      const formGroup = field.closest('.form-group');

      let errorList = formGroup.querySelector(
        '.help-block.sonata-ba-field-error-messages .list-unstyled'
      );

      if (!errorList) {
        const errorWrapper = document.createElement('div');
        errorWrapper.classList.add('help-block', 'sonata-ba-field-error-messages');

        errorList = document.createElement('ul');
        errorList.classList.add('list-unstyled');

        formGroup.classList.add('has-error');

        errorWrapper.appendChild(errorList);
        formGroup.appendChild(errorWrapper);
      }

      const errorItem = document.createElement('li');
      errorItem.innerHTML = `<i class="fas fa-exclamation-circle" aria-hidden="true"></i> ${violation.title}`;

      errorList.appendChild(errorItem);
    });

    return content;
  }

  /**
   * The dialog is moved to the document body on first use, as nested forms break the HTML.
   *
   * @returns {jQuery}
   */
  initializeDialog() {
    if (!this.dialog) {
      this.dialog = jQuery(this.dialogTarget);

      document.body.appendChild(this.dialog.get(0));

      this.log('move dialog container as a document child');
    }

    return this.dialog;
  }

  /**
   * @returns {jQuery}
   */
  getDialogContent() {
    return this.initializeDialog().find('.modal-body').first();
  }

  log(message) {
    window.Admin.log(`[${this.inputTarget.id}|model_list] ${message}`);
  }
}
