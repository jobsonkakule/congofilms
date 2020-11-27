import Tips from './modules/tips'
import 'slick-carousel'
import 'slick-carousel/slick/slick.css'
import 'slick-carousel/slick/slick-theme.css'
import 'nouislider/distribute/nouislider.css'

let $ = require('jquery')

// require('../scss/app.scss');
require('../scss/style.scss');

$('[data-slider]').slick({
    dots: true,
    arrows: true
})

// Delete

document.querySelectorAll('[data-delete]').forEach(a => {
  a.addEventListener('click', e => {
      e.preventDefault()
      fetch(a.getAttribute('href'), {
          method: 'DELETE',
          headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json'
          },
          body: JSON.stringify({'_token': a.dataset.token})
      }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  a.parentNode.parentNode.removeChild(a.parentNode)
                  
              } else {
                  alert(data.error)
              }
          })
          .catch(e => e.alert(e))
  })
})

// Top button

$(document).ready(function(){
    $(window).scroll(function(){
      if($(this).scrollTop() > 150) {
        $('.gotop').fadeIn();
      } else {
        $('.gotop').fadeOut();
      }
    });
    $('.gotop').on('click', function(){
      $('html, body').scrollTop(0);
      return false;
    })
});

// Responsive button
Tips.iconBar()
Tips.photo()
