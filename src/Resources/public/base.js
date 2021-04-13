jQuery(document).ready(function() {
    SonataCore.remove_iCheck_in_flashmessage();
    SonataCore.addFlashmessageListener();
});

var SonataCore = {
    remove_iCheck_in_flashmessage: () => {
        jQuery('.read-more-state').iCheck('destroy');
    },
    addFlashmessageListener: () => {
        document.querySelectorAll('.read-more-state').forEach((element) => {
            element.addEventListener('change', (event) => {
                let label = document.querySelector('label[for="' + element.id + '"]')
                let labelMore = label.querySelector('.more')
                let labelLess = label.querySelector('.less')

                if (event.target.checked) {
                    labelMore.classList.add('hide')
                    labelLess.classList.remove('hide')
                } else {
                    labelMore.classList.remove('hide')
                    labelLess.classList.add('hide')
                }
            });
        })
    }
};
