import Places from 'places.js'
import Map from './modules/map'
import Tips from './modules/tips'
import 'slick-carousel'
import 'slick-carousel/slick/slick.css'
import 'slick-carousel/slick/slick-theme.css'

Map.init()

let inputAdress = document.querySelector('#property_adress')
if (inputAdress !== null) {
    let place = Places({
        container: inputAdress
    })
    place.on('change', e => {
        document.querySelector('#property_city').value = e.suggestion.city
        document.querySelector('#property_postal_code').value = e.suggestion.postcode
        document.querySelector('#property_lat').value = e.suggestion.latlng.lat
        document.querySelector('#property_lng').value = e.suggestion.latlng.lng
    })
}

let searchAdress = document.querySelector('#search_adress')
if (searchAdress !== null) {
    let place = Places({
        container: searchAdress
    })
    place.on('change', e => {
        document.querySelector('#lat').value = e.suggestion.latlng.lat
        document.querySelector('#lng').value = e.suggestion.latlng.lng
    })
}


let $ = require('jquery')

require('../css/app.css');
require('../scss/app.scss');

require('select2')

$('[data-slider]').slick({
    dots: true,
    arrows: true
})
// $('select').select2()
// let $contactButton = $('#contactButton')
// $contactButton.click(e => {
//     e.preventDefault()
//     $('#contactForm').slideToggle();
// })

// Suppression des elements

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

// Last news carousel
$('.carousel-inner div:first-child').addClass('active')


// Responsive button
Tips.iconBar()

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');
