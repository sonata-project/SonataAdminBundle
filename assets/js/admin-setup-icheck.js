/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import jQuery from 'jquery';
import { getConfig } from './admin-config';
import log from './admin-log';

/**
 * @param {HTMLElement} subject
 * @returns {void}
 */
const setupICheck = (subject) => {
  if (getConfig('USE_ICHECK')) {
    log('[core|setup_icheck] configure iCheck on', subject);

    const inputs = jQuery('input[type="checkbox"]:not(.read-more-state, label.btn > input, [data-sonata-icheck="false"]), input[type="radio"]:not(label.btn > input, [data-sonata-icheck="false"])', subject);

    inputs.iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
    });

    // In case some checkboxes were already checked (for instance after moving
    // back in the browser's session history) update iCheck checkboxes.
    if (subject === window.document) {
      setTimeout(() => {
        inputs.iCheck('update');
      }, 0);
    }
  }
};

export default setupICheck;
