/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { getMetaContent } from './utils.js';

class Config {
    params = null;

    param(key) {
        if (this.params === null) {
            try {
                const content = getMetaContent('sensiolabs-config') || getMetaContent('sonata-config');
                this.params = content ? JSON.parse(content) : {};
            } catch (e) {
                console.warn(`An error has occurred resolving the config meta tag: ${e.message}.`);
                this.params = {};
            }
        }

        if (key in this.params) {
            return this.params[key];
        }

        return null;
    }
}

export default new Config();
