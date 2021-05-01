/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @returns {void}
 */
const addFlashMessageListener = () => {
  document.querySelectorAll('.read-more-state').forEach((element) => {
    element.addEventListener('change', (event) => {
      const label = document.querySelector(`label[for="${element.id}"]`);
      const labelMore = label.querySelector('.more');
      const labelLess = label.querySelector('.less');

      if (event.target.checked) {
        labelMore.classList.add('hide');
        labelLess.classList.remove('hide');
      } else {
        labelMore.classList.remove('hide');
        labelLess.classList.add('hide');
      }
    });
  });
};

export default addFlashMessageListener;
