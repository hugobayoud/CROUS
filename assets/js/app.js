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

// AJOUTER/SUPPRIMER UN FORMULAIRE PROTOTYPE (Utile dans la gestion des périodes pour la page "Gestion des DSI")
// On calcule le nombre de users enregsitrés
var $countMe = $('.panel').length;
var $collectionHolders = [];
var $addTagButtons = [];
var $newLinks = [];

for (let $i = 0; $i < $countMe; $i++) {
	$addTagButtons.push($('<button type="button" class="add_tag_link add-button">Ajouter une période</button>'));
	$newLinks.push($('<div class="vert-middle"></div>').append($addTagButtons[$i])); 
}

jQuery(document).ready(function() {
	for (let $i = 0; $i < $countMe; $i++) {
		// Get the ul that holds the collection of tags
		$collectionHolders[$i] = $('div.dsis_' + $i);

		// add a delete link to all of the existing tag form li elements
		$collectionHolders[$i].find('div.form-row-center').each(function() {
			addTagFormDeleteLink($(this));
		});

		// add the "Ajouter une période" anchor and li to the tags ul
		$collectionHolders[$i].append($newLinks[$i]);

		// count the current form inputs we have for the actual user
		// in order to get a new index when inserting a new item
		$collectionHolders[$i].data('index', $collectionHolders[$i].find('input').length);

		// On appelle la fonction addTagForm() dès lors que l'on clique sur le bouton "Ajouter une période"
		$addTagButtons[$i].on('click', function(e) {
			addTagForm($collectionHolders[$i], $newLinks[$i]);
		});	
	}
});

function addTagForm($collectionHolder, $newLinkLi) {
	// Get the data-prototype explained earlier
	var prototype = $collectionHolder.data('prototype');

	// get the new index
	var index = $collectionHolder.data('index');

	var newForm = prototype;

	// Replace '__name__' in the prototype's HTML with the index to be sure to have a unique id
	newForm = newForm.replace(/__name__/g, index);

	// increase the index with one for the next item
	$collectionHolder.data('index', index + 1);

	// Display the form in the page in an li, before the "Add a tag" link li
	var $newFormLi = $('<div class="text-center form-row-center" style="background: lightgrey; padding: 10px;"><div class="col-md-4"></div><div class="col-md-4"></div></div>').append(newForm);
	$newLinkLi.before($newFormLi);

	// add a delete link to the new form
	addTagFormDeleteLink($newFormLi);
}

function addTagFormDeleteLink($tagFormLi) {
	var $removeFormButton = $('<div class="col-md-4" style="padding-top: 25px;"><button type="button" class="remove-button">Supprimer cette période</button></div>');
	$tagFormLi.append($removeFormButton);

	$removeFormButton.on('click', function(e) {
		// remove the li for the tag form
		$tagFormLi.remove();
	});
}



// FIN DU FICHIER app.js
console.log('Fin du fichier "assets/js/app.js"');
