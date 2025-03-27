((Drupal, once) => {
  Drupal.behaviors.at_button = {
    attach(context) {
      once('button', '.button', context).forEach((item) => {
        item.addEventListener('click', (event) => {
          event.preventDefault();
          alert(item.dataset.message);
        });
      });
    },
  };
})(Drupal, once);
