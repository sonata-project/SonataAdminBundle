/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import jQuery from 'jquery';
import log from './admin-log';

const collectionCounters = [];

/**
 * Count and save element of each collection
 *
 * @param {HTMLElement} subject
 * @returns {void}
 */
const setupCollectionCounter = (subject) => {
  log('[core|setup_collection_counter] setup collection counter', subject);

  const highestCounterRegexp = new RegExp('_([0-9]+)[^0-9]*$');

  jQuery(subject).find('[data-prototype]').each((index, element) => {
    const collection = jQuery(element);
    let counter = -1;

    collection.children().each((collectionIndex, collectionElement) => {
      const matches = highestCounterRegexp.exec(jQuery('[id^="sonata-ba-field-container"]', collectionElement).attr('id'));
      if (matches && matches[1] && matches[1] > counter) {
        counter = parseInt(matches[1], 10);
      }
    });

    collectionCounters[collection.attr('id')] = counter;
  });
};

const setupCollectionButtons = (subject) => {
  jQuery(subject).on('click', '.sonata-collection-add', (event) => {
    event.preventDefault();

    const container = jQuery(event.target).closest('[data-prototype]');

    collectionCounters[container.attr('id')] += 1;

    const counter = collectionCounters[container.attr('id')];
    let proto = container.attr('data-prototype');
    const protoName = container.attr('data-prototype-name') || '__name__';
    // Set field id
    const idRegexp = new RegExp(`${container.attr('id')}_${protoName}`, 'g');
    proto = proto.replace(idRegexp, `${container.attr('id')}_${counter}`);

    // Set field name
    const parts = container.attr('id').split('_');
    const nameRegexp = new RegExp(`${parts[parts.length - 1]}\\]\\[${protoName}`, 'g');
    proto = proto.replace(nameRegexp, `${parts[parts.length - 1]}][${counter}`);
    jQuery(proto)
      .insertBefore(jQuery(event.target).parent())
      .trigger('sonata-admin-append-form-element');
    jQuery(event.target).trigger('sonata-collection-item-added');
  });

  jQuery(subject).on('click', '.sonata-collection-delete', (event) => {
    event.preventDefault();

    jQuery(event.target).trigger('sonata-collection-item-deleted');
    jQuery(event.target).closest('.sonata-collection-row').remove();
    jQuery(document).trigger('sonata-collection-item-deleted-successful');
  });
};

export { setupCollectionCounter, setupCollectionButtons };
