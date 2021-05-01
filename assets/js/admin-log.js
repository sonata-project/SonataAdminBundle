/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { getConfig } from './admin-config';

/**
 * @param  {...any} args
 * @returns {void}
 */
const log = (...args) => {
  if (!getConfig('DEBUG')) {
    return;
  }

  const message = `[Sonata.Admin] ${args.join(', ')}`;

  // eslint-disable-next-line no-console
  console.debug(message);
};

export default log;
