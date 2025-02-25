/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { getMetaContent } from './utils';

class Translation {
  messages = null;

  trans(key) {
    if (this.messages === null) {
      this.messages = JSON.parse(getMetaContent('sonata-translations'));
    }

    if (key in this.messages) {
      return this.messages[key];
    }

    return null;
  }
}

export default new Translation();
