/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// eslint-disable-next-line import/no-extraneous-dependencies
import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';
import { startStimulusApp } from '@symfony/stimulus-bridge';

// eslint-disable-next-line import/prefer-default-export
export const sonataApplication = startStimulusApp();

const definitions = definitionsFromContext(
  require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
  )
);

definitions.forEach((definition) => {
  definition.identifier = `sonata-${definition.identifier}`;
});

sonataApplication.load(definitions);
