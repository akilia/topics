<?php
/**
 * Gestion du formulaire de d'abonnement aux notifications sur les commentaires d'un objet
 *
 * @plugin     Sujets de Forum
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Topics\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Chargement du formulaire d'édition de topic
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @param int|string $id_notifsubscriber
 *     Identifiant de notifiactions_abonnement. 'new' pour une nouvelle notification d'abonnement.
 * @return array
 *     Environnement du formulaire
 */


function formulaires_subscribe_notification_charger_dist($id_notifsubscriber = 'new', $id_auteur = 0, $id_objet = 0, $objet='') {
	$valeurs = array();

	$id_auteur = verifier_session();

	if ($subscriber = sql_fetsel('id_notifsubscriber, actif', 'spip_notifsubscribtions', 'id_auteur='.intval($id_auteur).' and id_objet='.intval($id_objet).' and objet='.sql_quote($objet))) {
		$valeurs['id_notifsubscriber'] = $subscriber['id_notifsubscriber'];
		$valeurs['actif'] = $subscriber['actif'];
	}

	return $valeurs;
}

/**
 * Vérifications du formulaire d'édition de topic
 *
 * Vérifier les champs postés et signaler d'éventuelles erreurs
 *
 * @param int|string $id_notifsubscriber
 *     Identifiant de notifiactions_abonnement. 'new' pour une nouvelle notification d'abonnement.
 * @return array
 *     Tableau des erreurs
 */
function formulaires_subscribe_notification_verifier_dist($id_notifsubscriber = 'new', $id_auteur = 0, $id_objet = 0, $objet='') {
	$erreurs = array();
	if (!verifier_session()) {
		$erreurs['message_erreur'] = "Vous devez être connecté pour activer ou désactiver votre abonnemnt à ce $objet.";
	}

	if ($id_objet == 0 or empty($objet)) {
		$erreurs['message_erreur'] = "Il faut pouvoir associer un objet.";
	}
	return $erreurs;
}

/**
 * Traitement du formulaire d'édition de topic
 *
 * Traiter les champs postés
 *
 * @param int|string $id_notifsubscriber
 *     Identifiant de notifiactions_abonnement. 'new' pour une nouvelle notification d'abonnement.
 * @return array
 *     Retours des traitements
 */
function formulaires_subscribe_notification_traiter_dist($id_notifsubscriber = 'new', $id_auteur = 0, $id_objet = 0, $objet='') {
	
	$actif = _request('actif') ;

	if (is_numeric($id_notifsubscriber)) {
		$res = sql_updateq('spip_notifsubscribtions', array('actif' => $actif), 'id_auteur='.intval($id_auteur).' and id_objet='.intval($id_objet).' and objet='.sql_quote($objet));
	} else {
		$t = array('id_auteur'	=> $id_auteur,
					'id_objet'	=> $id_objet,
					'objet'		=> $objet,
					'date'		=> date('Y-m-d H:i:s'),
					'actif' 	=> $actif,
		);
		$res = sql_insertq('spip_notifsubscribtions', $t);
	}
	$retours['editable'] = true;

	if ($res and $actif == 'oui') {
		$retours['message_ok'] = "Vous revevrez désormais les notifications par emails des commentaires sur ce sujet.";
	}
	if ($res and $actif == 'non') {
		$retours['message_ok'] = "Vous ne recevrez plus désormais les notifications par emails des commentaires sur ce sujet.";
	}

	return $retours;
}
