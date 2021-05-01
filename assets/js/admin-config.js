/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

let config;
let translations;

/**
 * Reads configuration placed on the html with the
 * data attribute 'data-sonata-admin'
 *
 * @returns {void}
 */
const read = () => {
  const adminConfiguration = document.querySelector('[data-sonata-admin]');

  config = null;
  translations = null;

  if (adminConfiguration !== null) {
    const data = JSON.parse(adminConfiguration.dataset.sonataAdmin);

    config = data.config;
    translations = data.translations;
  }
};

/**
 * @param {string} key
 * @returns {mixed}
 */
const getConfig = (key) => {
  if (config === undefined) {
    read();
  }

  return config !== null ? config[key] : undefined;
};

/**
 * @param {string} key
 * @returns {mixed}
 */
const getTranslation = (key) => {
  if (translations === undefined) {
    read();
  }

  return translations !== null ? translations[key] : undefined;
};

export { getConfig, getTranslation, read };
