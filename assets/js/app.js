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

/* AJOUTER/SUPPRIMER UN FORMULAIRE PROTOTYPE
 *	Utilisé dans la gestion des périodes pour la page "Gestion des DSI", "Gestion des Valideurs", "Validation des demandes")
*/

// Nombre d'user enregistrés sur la page
var $countMe = $('.panel').length;
// Formulaires déjà existants
var $formsHolders = [];
// Bandeau de chaque user pour ajout du bouton "AJOUTER" au bon endroit dans la detail-box
var $bannerHolders = [];
// Boutons "AJOUTER" pour chaque user
var $addButtons = [];
// Div où vont s'ajouter les nouveaux formulaires après clic sur "AJOUTER"
var $newFormContainers = [];
// Div où va s'ajouter le bouton "AJOUTER" pour chaque user
var $addButtonContainers = [];

for (let $i = 0; $i < $countMe; $i++) {
	$addButtons.push($('<button type="button" class="add-button">Ajouter</button>'));
	$addButtonContainers.push($('<div class="add-button-div"></div>').append($addButtons[$i]));
	$newFormContainers.push($('<div class="add-div"></div>'));
}

jQuery(document).ready(function() {
	for (let $i = 0; $i < $countMe; $i++) {
		// Récupérer la div qui contient toutes les formulaires d'un user déjà enregistrés en base
		$formsHolders[$i] = $('.form_' + $i);
		$bannerHolders[$i] = $('.banner_' + $i);

		// Ajouter le bouton "RETIRER" à chaque formulaire déjà existant
		$formsHolders[$i].find('.form-row-center').each(function() {
			addTagFormDeleteLink($(this));
		});

	 	// Compter le nombre de formulaires pour obtenir un index unique
	 	$formsHolders[$i].data('index', $formsHolders[$i].find('input').length);
		
	 	// Ajouter le bouton "AJOUTER" après le paragraphe en haut de la div detail-box
		$bannerHolders[$i].append($addButtonContainers[$i]);

		// Ajouter la div "add-div" pour les nouveaux formulaires dans la div detail-box
		$formsHolders[$i].append($newFormContainers[$i]);
		
		// Appeler la fonction addTagForm() dès lors que l'on clique sur le bouton "Ajouter une période"
		$addButtons[$i].on('click', function(e) {
			addTagForm($formsHolders[$i], $i);
		});
	}
});

function addTagForm($formsHolder, indice) {
	// Récupérer le prototype du formulaire comme nouveau formulaire
	var formPrototype = $formsHolder.data('prototype');

	// Récupérer un nouvel index pour une classe unique
	var index = $formsHolder.data('index');

	// Changer le nom du form créé pour s'assurer une classe unique
	formPrototype = formPrototype.replace(/__name__/g, index);

	// Incrémenter l'index pour le prochain item
	$formsHolder.data('index', index + 1);

	// Ajouter le nouveau formulaire dans une div (pour un background-color: grey;)
	var $newForm = $('<div class="add-new-form"></div>').append(formPrototype);

	// Ajouter le nouveau formulaire à la fin du grand formulaire
	$('.form_' + indice).append($newForm);

	// Ajouter le bouton "RETIRER" pour chaque formulaire
	addTagFormDeleteLink($newForm);
}

function addTagFormDeleteLink($form) {
	// Création du bouton "RETIRER"
	var $removeFormButton = $('<div class="col-md-3"><button type="button" class="remove-button">Retirer</button></div>');
	
	// Ajouter le bouton "RETIRER" pour périodes déjà existantes
	$form.append($removeFormButton);

	// Ajout le bouton "RETIRER" pour les périodes ajoutées
	$form.find('.form-row-center').append($removeFormButton);

	// Retirer le formulaire sur le clique du bouton "RETIRER"
	$removeFormButton.on('click', function(e) {
		$form.remove();
	});
}



// FIN DU FICHIER app.js
console.log('Fin du fichier "assets/js/app.js"');
