/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

 // any CSS you import will output into a single css file (app.css in this case)
require('../css/app.css');

let $ = require('jquery')
require('select2');

$('select').select2()

// let $detailLink = $('.detail_link')
// $detailLink.click(e => {
// 	e.preventDefault();
// 	$('.userForm').slideToggle();
// })

// let link_ids = document.querySelectorAll('.detail_link');
// let form_ids = document.querySelectorAll('.uf');
let $link_class = $('.detail_link');
let $form_class = $('.uf');


$(".detail").on("click", function() {
    $(this).nextUntil('.userForm').next().slideToggle("slow");
});


// let arr_link_ids = Array.prototype.map.call(link_ids, function(el) {
//     return el.id;
// });
// let arr_form_ids = Array.prototype.map.call(form_ids, function(el) {
//     return el.id;
// });

// $($link_class).each(function() {
//     $(this).click(e => {
// 		e.preventDefault();
// 		$form_class.slideToggle();
// 	})
// });

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';

// console.log(arr_link_ids);
// console.log(arr_form_ids);
console.log('Fin du fichier "assets/js/app.js"');
