<?php
/**
 * Notifications plugin TOPICS
 *
 * @plugin     TOPICS
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\topics\Notifications
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Composition et Envoi à tous les auteurs du site de la notification de création d'un nouveau sujet dans le forum
 *
 * @param string $quoi
 *   Événement de notification
 * @param int $id_topic
 *     Identifiant de l’article
 * @param array $option
 *     liste d'option
 */
function notifications_nouveausujet_dist($quoi, $id_topic, $options) {
	include_spip('inc/texte');
	include_spip('inc/config');
	
	// Destinataires : en fonction de l'option choisie en paramètrage du plugin, récupérer les emails des destinataires
	$destinataires = array();
	$qui = lire_config('topics/notification/qui');
	switch ($qui) {
		case 'webmestres':
			$where = "webmestre='oui' AND statut!='poubelle'";
			break;
		case 'admins':
			$where = "statut='0minirezo' AND statut!='poubelle'";
			break;
		case 'tous':
			$where = "statut IN ('0minirezo', '1comite') AND statut!='poubelle'";
			break;
		case 'webmaster':
		default:
			$email_webmaster = lire_config('email_webmaster');
			$where = "email=".sql_quote($email_webmaster);
			break;
	}
	
	$res = sql_allfetsel('email', 'spip_auteurs', $where);
	$destinataires = array_column($res, 'email');
	$destinataires = array_filter($destinataires, 'strlen'); //  virer les auteurs qui n'ont pas d'email


	// Sujet du mail : récupérer le titre du sujet
	$titre = sql_getfetsel('titre', 'spip_topics', 'id_topic='.intval($id_topic));
	$nom_site = lire_config('nom_site');
	$sujet  = 'Forum '.$nom_site.' : '.$titre;

	// Contenu du message : on défini le modèle voulu
	$modele = 'notifications/sujet_forum';

	// Email_from : c'est l'adresse email enregistrée dans la configuration, sinon, par défaut c'est l'email de l'auteur·e du sujet
	$email_from = lire_config('topics/notification/email_from');
	if (!$email_from or strlen($email_from) == 0) {
		if ($id_auteur 	= sql_getfetsel('id_auteur', 'spip_auteurs_liens', 'objet='.sql_quote('topic').' AND id_objet='.intval($id_topic))) {
			$email_from = sql_getfetsel('email', 'spip_auteurs', 'id_auteur='.intval($id_auteur));
		}
	}

	// hop, on envoi
	if (
		count($destinataires) > 0 
		and $email_from
	) {
		$message = email_notification_objet($id_topic, 'topic', $modele);
		$envoyer_mail = charger_fonction('envoyer_mail', 'inc/');
		$mail = $envoyer_mail($destinataires, $sujet, $message, $email_from);
	}
}
