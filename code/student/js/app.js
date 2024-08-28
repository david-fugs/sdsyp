var open_menu_button = document.querySelector('.open-menu');
var menu = document.querySelector('header nav ul');
  
open_menu_button.addEventListener('click', function() {
    if (menu.style.display == 'block') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'block';
    }
});