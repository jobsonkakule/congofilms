import Tips from './modules/tips'
import Filter from './modules/filter'
import PostToggle from './modules/postToggle'
import RandomPosts from './modules/randomPost'
import 'slick-carousel'
import 'slick-carousel/slick/slick.css'
import 'slick-carousel/slick/slick-theme.css'
import tinymce from 'tinymce/tinymce'
import noUiSlider from 'nouislider'
import 'nouislider/distribute/nouislider.css'

import 'tinymce/themes/silver'
new Filter(document.querySelector('.js-filter'))
new PostToggle(document.querySelector('.js-show'))
new RandomPosts(document.querySelector('.js-home'))

// Tagsinput
// import './tagsinput/tagsinput.css'
// import './tagsinput/tagsinput.js'
// import './tagsinput/typeahead.js'

// Any plugins you want to use has to be imported
import 'tinymce/plugins/paste'
import 'tinymce/plugins/link'
import 'tinymce/plugins/image'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/media'

let $ = require('jquery')

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
// Serach-bar 

// $('#search-bar').hide()
$('.search-link').click(function(e) {
    e.preventDefault()
    $('#search-bar').slideToggle()
})
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


document.querySelectorAll('[comment-delete]').forEach(a => {
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
                    a.parentNode.parentNode.parentNode.parentNode.parentNode.removeChild(a.parentNode.parentNode.parentNode.parentNode)
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

// Tagsinput
// let tags = new Bloodhound({
//     prefetch: '/tags.json',
//     datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
//     queryTokenizer: Bloodhound.tokenizers.whitespace

// })
// $('.tag-input').tagsinput({
//     typeaheadjs: [{
//         highlights: true
//     }, {
//         name: 'tags',
//         display: 'name',
//         value: 'name',
//         source: tags
//     }]
// })
// Copy image Link


// Tinymce

tinymce.init({
    selector: '#post_content',
    setup: function (editor) {
        editor.on('change', function (e) {
            editor.save();
        });
    },
    plugins: ['paste', 'link', 'image', 'lists', 'media'],
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media",
    image_description: true,
    image_caption: true,
    video_template_callback: function(data) {
        return '<video width="' + data.width + '" height="' + data.height + '"' + (data.poster ? ' poster="' + data.poster + '"' : '') + ' controls="controls">\n' + '<source src="' + data.source1 + '"' + (data.source1mime ? ' type="' + data.source1mime + '"' : '') + ' />\n' + (data.source2 ? '<source src="' + data.source2 + '"' + (data.source2mime ? ' type="' + data.source2mime + '"' : '') + ' />\n' : '') + '</video>';
    },
    // mediaembed_service_url: "SERVICE_URL",
    // mediaembed_max_width: 450
});

// Slider
const slider = document.getElementById('score-slider');
if (slider) {
    const min = document.getElementById('min')
    const max = document.getElementById('max')
    const minValue = parseInt(slider.dataset.min, 10)
    const maxValue = parseInt(slider.dataset.max, 10)
    const range = noUiSlider.create(slider, {
        start: [min.value || minValue, max.value || maxValue],
        connect: true,
        range: {
            'min': minValue,
            'max': maxValue
        }
    });
    range.on('slide', function (values, handle) {
        if (handle === 0) {
            min.value = Math.round(values[0])
        }
        if (handle === 1) {
            max.value = Math.round(values[1])
        }
    })
    range.on('end', function (values, handle) {
        min.dispatchEvent(new Event('change'))
    })
}

// Responsive button
Tips.iconBar()
Tips.comment()
Tips.replyComment()
Tips.copyLink()
// Tips.responsiveEmbed()
Tips.socialButtons()
// Tips.toggleAuthor()

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

// console.log('Hello Webpack Encore! Edit me in assets/js/app.js');
