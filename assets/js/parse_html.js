/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default function parseHTML(html) {
  const template = document.createElement('template');
  template.innerHTML = html;

  return document.importNode(template.content, true);
}