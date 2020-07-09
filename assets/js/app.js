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
	var show = localStorage.getItem('show');
	var cible = localStorage.getItem('target');
	if (show === 'true'){
		$(cible).show();
		localStorage.removeItem('show');
		localStorage.removeItem('target');
		$('html,body').animate({scrollTop: document.body.scrollHeight}, "fast");

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

		if (table) {
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
	}
});

// AJOUTER/SUPPRIMER UN FORMULAIRE PROTOTYPE (Utile dans la gestion des périodes pour la page "Gestion des DSI", "Gestion des Valideurs", "Validation des demandes")
// On calcule le nombre de users enregsitrés
var $countMe = $('.panel').length;
var $collectionHolders = [];
var $otherCollectionHolders = [];
var $addTagButtons = [];
var $newLinks = [];
var $newNewLinks = [];

for (let $i = 0; $i < $countMe; $i++) {
	//$addTagButtons.push($('<button type="button" class="add_tag_link add-button">Ajouter</button>'));
	$addTagButtons.push($('<button type="button" class="add-button">Ajouter</button>'));
	$newNewLinks.push($('<div class="add-button-div"></div>').append($addTagButtons[$i]));
	$newLinks.push($('<div class="add-div"></div>'));
}

jQuery(document).ready(function() {
	for (let $i = 0; $i < $countMe; $i++) {
		// Récupérer la div qui contient toutes les périodes d'un user
		$collectionHolders[$i] = $('.form_' + $i);
		$otherCollectionHolders[$i] = $('.banner_' + $i);

		// Ajouter le bouton "RETIRER" à chaque formulaire déjà existant
		$collectionHolders[$i].find('.form-row-center').each(function() {
			addTagFormDeleteLink($(this));
		});

	 	// Compter le nombre de formulaires pour obtenir un index unique
	 	$collectionHolders[$i].data('index', $collectionHolders[$i].find('input').length);
		
	 	// Ajouter le bouton "AJOUTER" après le paragraphe en haut de la div detail-box
		//$('.info-p').after($newLinks[$i]);
		//$collectionHolders[$i].prepend($newLinks[$i]);
		$otherCollectionHolders[$i].append($newNewLinks[$i]);
		$collectionHolders[$i].append($newLinks[$i]);
		
		// Appeler la fonction addTagForm() dès lors que l'on clique sur le bouton "Ajouter une période"
		$addTagButtons[$i].on('click', function(e) {
			addTagForm($collectionHolders[$i], $i);
		});
	}
});

function addTagForm($collectionHolder, indice) {
	// Récupérer le prototype du formulaire comme nouveau formulaire
	var newForm = $collectionHolder.data('prototype');

	// Récupérer un nouvel index pour une classe unique
	var index = $collectionHolder.data('index');
	newForm = newForm.replace(/__name__/g, index);

	// Incrémenter l'index pour le prochain item
	$collectionHolder.data('index', index + 1);

	// Ajouter le nouveau formulaire dans une div (background-color: grey;)
	var $newFormLi = $('<div class="add-new-form"></div>').append(newForm);

	// Ajouter le nouveau formulaire à la fin du grand formulaire
	$('.form_' + indice).append($newFormLi);

	// Ajouter le bouton "RETIRER" pour chaque formulaire
	addTagFormDeleteLink($newFormLi);
}

function addTagFormDeleteLink($tagFormLi) {
	// Création du bouton "RETIRER"
	var $removeFormButton = $('<div class="col-md-3"><button type="button" class="remove-button">Retirer</button></div>');
	
	// Ajouter le bouton "RETIRER" pour périodes déjà existantes
	$tagFormLi.append($removeFormButton);

	// Ajout le bouton "RETIRER" pour les périodes ajoutées
	$tagFormLi.find('.form-row-center').append($removeFormButton);

	// Retirer le formulaire sur le clique du bouton "RETIRER"
	$removeFormButton.on('click', function(e) {
		$tagFormLi.remove();
	});
}



// FIN DU FICHIER app.js
console.log('Fin du fichier "assets/js/app.js"');
