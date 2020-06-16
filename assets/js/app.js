/*
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
require('../css/app.css');

// JQUERY
let $ = require('jquery')

// SELECT2
require('select2');
$('select').select2()

// CACHER/MONTRER DETAILS D'UN TABLEAU APRES CLIQUE
$(document).ready(function(){	
	$(".flip").on("click", function(e) {  
		var target = $(this).attr("href");
		$(target).toggle();
		$(".panel").not(target).hide();
		e.preventDefault();
	});
});

// Fin du fichier app.js
console.log('Fin du fichier "assets/js/app.js"');
