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


/* CACHER/MONTRER DETAILS D'UN TABLEAU APRES CLIC et GERER L'AFFICHAGE DE LA LIGNE SELECTIONNEE
 * Utilisé dans tous les tableaux où les lignes ont une detail-box associées
*/
jQuery(document).ready(function(){
	var currentTarget = localStorage.getItem('currentTarget');
	// Si currentTarget est défini : une detail-box etait ouverte au moment où un form a été envoyé
	if (currentTarget) {
		// On réouvre la div
		$(currentTarget).show();
		// On regrise la ligne du tableau
		changeColor(currentTarget);
		// on scroll en bas de la page
		$('html,body').animate({scrollTop: document.body.scrollHeight}, "fast");
		localStorage.removeItem("currentTarget");
	}

	$(".flip").on("click", function(e) {
		// Div panel sur laquelle on clique pour voir ou cacher les infos 
		var target = $(this).attr("href");
		// Ouvrir/Fermer la div panel cliquée
		$(target).toggle();
		// Fermer toutes les div panel sauf celle cliquée
		$(".panel").not(target).hide();
		e.preventDefault();

		// Si la detail-box est ouverte en cliquant sur le pinceau, on scroll down
		if (!$(target).is(':hidden')) {
			$('html,body').animate({scrollTop: document.body.scrollHeight},"slow");
		}

		// Changer la couleur des lignes du tableau
		changeColor(target);
	});

	// En cliquant sur le bouton "ENREGISTRER", on retient l'info qu'après avoir rechargé la page, il faut scroll down
	$("#save-div").click(function() {
		console.log(this);
		console.log("this");
 		localStorage.setItem('currentTarget', true);
	});

	// Changement de la couleur des lignes du tableau suivant si une detail-box est ouverte ou non
	function changeColor(target) {
		// Récupérer l'element TABLE de la page (s'il existe)
		var table = document.getElementById("table");
		if (table) {
			// Enlever la classe "selected-row" de toutes les lignes du tableau
			for(var i = 0; i < table.rows.length - 1; i++) {
				table.rows[i+1].classList.remove("selected-row");
			}

			if (target) {
				// SSI une detail-box est ouverte, ajouter la classe "selected-row"
				if (!$(target).is(':hidden')) {
					// Récupérer l'indice de la detail-box ouverte
					var indice = parseInt(target.split('_')[2]);
					// Ajouter la class "selected"
					table.rows[indice + 1].classList.add("selected-row");
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
