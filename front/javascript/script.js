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

var editor = document.querySelector( '#editor' );
CKEDITOR.replace('editor');

// Get the input field
var input = document.getElementById("focusInput");

// Execute a function when the user presses a key on the keyboard
input.addEventListener("keypress", function(event) {
  // If the user presses the "Enter" key on the keyboard
  if (event.key === "Enter") {
    // Cancel the default action, if needed
    event.preventDefault();
    // Trigger the button element with a click
    document.getElementById("focus").click();
  }
}); 