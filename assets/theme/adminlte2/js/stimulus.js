/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// eslint-disable-next-line import/no-extraneous-dependencies
import { Application } from '@hotwired/stimulus';
import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';

export const sonataApplication = Application.start();

const definitions = definitionsFromContext(
  require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!../../../shared/controllers',
    true,
    /\.[jt]sx?$/
  )
);

definitions.forEach((definition) => {
  definition.identifier = `sonata-${definition.identifier}`;
});

sonataApplication.load(definitions);
