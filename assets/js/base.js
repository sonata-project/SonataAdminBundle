/*!
 * This file is part of the SensioLabs Admin Bundle package.
 *
 * (c) SensioLabs
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const SensioLabsCore = {
    addFlashmessageListener() {
        document.querySelectorAll('.read-more-state').forEach((element) => {
            element.addEventListener('change', (event) => {
                const label = document.querySelector(`label[for="${element.id}"]`);
                if (!label) return;

                const labelMore = label.querySelector('.more');
                const labelLess = label.querySelector('.less');

                if (event.target.checked) {
                    labelMore?.classList.add('hidden');
                    labelLess?.classList.remove('hidden');
                } else {
                    labelMore?.classList.remove('hidden');
                    labelLess?.classList.add('hidden');
                }
            });
        });
    },
};

document.addEventListener('DOMContentLoaded', () => {
    SensioLabsCore.addFlashmessageListener();
});

export default SensioLabsCore;
