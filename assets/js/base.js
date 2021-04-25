const SonataCore = {
  remove_iCheck_in_flashmessage() {
    jQuery('.read-more-state').iCheck('destroy');
  },
  addFlashmessageListener() {
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
  },
};

jQuery(document).ready(() => {
  SonataCore.remove_iCheck_in_flashmessage();
  SonataCore.addFlashmessageListener();
});
