/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { getMetaContent } from './utils.js';

class Translation {
    messages = null;

    trans(key) {
        if (this.messages === null) {
            try {
                const content = getMetaContent('sensiolabs-translations') || getMetaContent('sonata-translations');
                this.messages = content ? JSON.parse(content) : {};
            } catch (e) {
                console.warn(`An error has occurred resolving the translations meta tag: ${e.message}.`);
                this.messages = {};
            }
        }

        if (key in this.messages) {
            return this.messages[key];
        }

        return null;
    }
}

export default new Translation();
