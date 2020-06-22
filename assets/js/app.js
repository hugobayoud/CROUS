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

// CACHER/MONTRER DETAILS D'UN TABLEAU APRES CLIQUE et GERE L'AFFICHAGE DE LA LIGNE SELECTIONNEE
$(document).ready(function(){
	//var table = document.getElementById("table");

	var show = localStorage.getItem('show');
	var cible = localStorage.getItem('target');
	if (show === 'true'){
		$(cible).show();
		localStorage.removeItem('show');
		localStorage.removeItem('target');
		$('html,body').animate({scrollTop: document.body.scrollHeight}, "fast");

		//var index = target.substring(target.length -1, target.length);
		//table.rows[index+1].classList.add("selected-row");
	}

	var index = localStorage.getItem('index');
	if (index > -1) {
		changeColor(index);
	}
	localStorage.removeItem('index');
	

	$(".flip").on("click", function(e) {  
		var target = $(this).attr("href");
		var indice = target.substring(target.length -1, target.length);

		$(target).toggle();
		$(".panel").not(target).hide();
		e.preventDefault();

		$('html,body').animate({scrollTop: document.body.scrollHeight},"slow");
		changeColor(parseInt(indice));
		
		localStorage.setItem('show', 'true'); //store state in localStorage
		localStorage.setItem('target', target); //store state in localStorage
	});


	function changeColor(indice) {
		var table = document.getElementById("table");

		for(var i = 0; i < table.rows.length - 1; i++) {
			if (i == indice) {
				// on ajoute la class "selected" dans la ligne du tableau
				table.rows[i+1].classList.add("selected-row");
				localStorage.setItem('index', indice); //store state in localStorage
			} else {
				// on enlève la classe "selected" a tous les autres
				table.rows[i+1].classList.remove("selected-row");
			}
		}
	}

	//--------------------------------------------------------------------------------
	// function showTable() {
	// 	$('#tableDiv').show();
	// 	localStorage.setItem('show', 'true'); //store state in localStorage
	// }
	
	// $(document).ready(function(){
	// 	var show = localStorage.getItem('show');
	// 	if(show === 'true'){
	// 		$('#tableDiv').show();
	// 	}
	// });
	//--------------------------------------------------------------------------------

});

// AJOUTER/SUPPRIMER UN FORMULAIRE PROTOTYPE (Utile dans la gestion des périodes pour la page "Gestion des DSI")
// On calcule le nombre de users enregsitrés
var $countMe = $('.panel').length;
var $collectionHolders = [];
var $addTagButtons = [];
var $newLinks = [];

for (let $i = 0; $i < $countMe; $i++) {
	$addTagButtons.push($('<button type="button" class="add_tag_link add-button">Ajouter</button>'));
	$newLinks.push($('<div class="add-div"></div>').append($addTagButtons[$i])); 
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
	var $newFormLi = $('<div class="add-new-form"></div>').append(newForm);
	$newLinkLi.before($newFormLi);

	// add a delete link to the new form
	addTagFormDeleteLink($newFormLi);
}

function addTagFormDeleteLink($tagFormLi) {
	var $removeFormButton = $('<div class="col-md-4"><button type="button" class="remove-button">Retirer</button></div>');
	$tagFormLi.append($removeFormButton);

	$removeFormButton.on('click', function(e) {
		// remove the li for the tag form
		$tagFormLi.remove();
	});
}



// FIN DU FICHIER app.js
console.log('Fin du fichier "assets/js/app.js"');
