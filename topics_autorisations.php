<?php
/**
 * Définit les autorisations du plugin Sujets de Forum
 *
 * @plugin     Sujets de Forum
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Topics\Autorisations
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Fonction d'appel pour le pipeline
 * @pipeline autoriser */
function topics_autoriser() {
}


// -----------------
// Objet topics


/**
 * Autorisation de voir un élément de menu (topics)
 *
 * @param  string $faire Action demandée
 * @param  string $type  Type d'objet sur lequel appliquer l'action
 * @param  int    $id    Identifiant de l'objet
 * @param  array  $qui   Description de l'auteur demandant l'autorisation
 * @param  array  $opt   Options de cette autorisation
 * @return bool          true s'il a le droit, false sinon
**/
function autoriser_topics_menu_dist($faire, $type, $id, $qui, $opt) {
	return true;
}


/**
 * Autorisation de créer (topic)
 *
 * @param  string $faire Action demandée
 * @param  string $type  Type d'objet sur lequel appliquer l'action
 * @param  int    $id    Identifiant de l'objet
 * @param  array  $qui   Description de l'auteur demandant l'autorisation
 * @param  array  $opt   Options de cette autorisation
 * @return bool          true s'il a le droit, false sinon
**/
function autoriser_topic_creer_dist($faire, $type, $id, $qui, $opt) {
	return (in_array($qui['statut'], array('0minirezo', '1comite')) and sql_countsel('spip_rubriques')>0);
}

/**
 * Autorisation de voir (topic)
 *
 * @param  string $faire Action demandée
 * @param  string $type  Type d'objet sur lequel appliquer l'action
 * @param  int    $id    Identifiant de l'objet
 * @param  array  $qui   Description de l'auteur demandant l'autorisation
 * @param  array  $opt   Options de cette autorisation
 * @return bool          true s'il a le droit, false sinon
**/
function autoriser_topic_voir_dist($faire, $type, $id, $qui, $opt) {
	return true;
}

/**
 * Autorisation de modifier (topic)
 *
 * @param  string $faire Action demandée
 * @param  string $type  Type d'objet sur lequel appliquer l'action
 * @param  int    $id    Identifiant de l'objet
 * @param  array  $qui   Description de l'auteur demandant l'autorisation
 * @param  array  $opt   Options de cette autorisation
 * @return bool          true s'il a le droit, false sinon
**/
function autoriser_topic_modifier_dist($faire, $type, $id, $qui, $opt) {
	return in_array($qui['statut'], array('0minirezo'));
}

/**
 * Autorisation de supprimer (topic)
 *
 * @param  string $faire Action demandée
 * @param  string $type  Type d'objet sur lequel appliquer l'action
 * @param  int    $id    Identifiant de l'objet
 * @param  array  $qui   Description de l'auteur demandant l'autorisation
 * @param  array  $opt   Options de cette autorisation
 * @return bool          true s'il a le droit, false sinon
**/
function autoriser_topic_supprimer_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('webmestre', '', '', $qui);
}

/**
 * Autorisation de créer l'élément (topic) dans une rubrique
 *
 * @param  string $faire Action demandée
 * @param  string $type  Type d'objet sur lequel appliquer l'action
 * @param  int    $id    Identifiant de l'objet
 * @param  array  $qui   Description de l'auteur demandant l'autorisation
 * @param  array  $opt   Options de cette autorisation
 * @return bool          true s'il a le droit, false sinon
**/
function autoriser_rubrique_creertopicdans_dist($faire, $type, $id, $qui, $opt) {
	return ($id and autoriser('voir', 'rubrique', $id) and autoriser('creer', 'topic', $id));
}

/**
 * Autorisation de dater un topic. Par défaut, seul les admins
 *
 * @param  string $faire Action demandée
 * @param  string $type  Type d'objet sur lequel appliquer l'action
 * @param  int    $id    Identifiant de l'objet
 * @param  array  $qui   Description de l'auteur demandant l'autorisation
 * @param  array  $opt   Options de cette autorisation
 * @return bool          true s'il a le droit, false sinon
**/
function autoriser_topic_dater_dist($faire, $type, $id, $qui, $opt) {
	return (in_array($qui['statut'], array('0minirezo')));
}

/**
 * Plugin LIM : compatibilité avec la Gestion de la restriction par rubrique
**/

if (!function_exists('autoriser_rubrique_creertopicdans') AND test_plugin_actif('lim')) {
	function autoriser_rubrique_creertopicdans($faire, $type, $id, $qui, $opt) {
		$quelles_rubriques = lire_config('lim_rubriques/topic');
		is_null($quelles_rubriques) ? $lim_rub = true : $lim_rub = !in_array($id,$quelles_rubriques);
 
		return
			$lim_rub
			AND autoriser_rubrique_creertopicdans_dist($faire, $type, $id, $qui, $opt);
	}
}


/*
 * Surcharge (sans _dist) de la fonction d'autorisation de vue d'un site, si pas déjà définie
 */
if (!function_exists('autoriser_topic_voir')) {
function autoriser_topic_voir($faire, $type, $id, $qui, $options) {
	include_spip('public/quete');
	include_spip('inc/accesrestreint');

	$publique = isset($options['publique']) ? $options['publique'] : !test_espace_prive();
	$id_auteur = isset($qui['id_auteur']) ? $qui['id_auteur'] : $GLOBALS['visiteur_session']['id_auteur'];

	// Si le site fait partie des contenus restreints directement, c'est niet
	if (in_array($id, accesrestreint_liste_objets_exclus('topicss', $publique, $id_auteur))) {
		return false;
	}

	if (!$id_rubrique = $options['id_rubrique']) {
		$site = quete_parent_lang('spip_topics', $id);
		$id_rubrique = $site['id_rubrique'];
	}

	return autoriser_rubrique_voir('voir', 'rubrique', $id_rubrique, $qui, $options);
}
}
