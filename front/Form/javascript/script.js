// Script permettant la création d'une liste déroulante (utilisé pour le bouton "téléphone" dans le formulaire de demande)
const dropdownBtns = document.querySelectorAll('.menu-btn');
let lastOpened = null;

dropdownBtns.forEach(btn => btn.addEventListener('click', function() {
  const menuContent = this.nextElementSibling;

  if (lastOpened !== null) {
    const target = lastOpened;
 
    target.addEventListener('animationend', () => {
      target.classList.remove('show', 'animate-out');
      this.classList.toggle('activated');
      if (target === lastOpened) {
        lastOpened = null;
      }
    }, {
      once: true
    });

    target.classList.add('animate-out');
  }

  if (lastOpened !== menuContent) {
    menuContent.classList.add('show');
    this.classList.toggle('activated');
    lastOpened = menuContent;
  }
}));