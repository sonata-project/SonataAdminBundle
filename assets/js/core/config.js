/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { getMetaContent } from './utils';

class Config {
  params = null;

  param(key) {
    if (this.params === null) {
      try {
        this.params = JSON.parse(getMetaContent('sonata-config'));
      } catch (e) {
        throw new Error(
          `An error has occurred resolving the "sonata-config" meta tag: ${e.message}.`
        );
      }
    }

    if (key in this.params) {
      return this.params[key];
    }

    return null;
  }
}

export default new Config();
