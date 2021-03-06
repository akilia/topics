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
	include_spip("inc/notifications");
	
	// Destinataires : en fonction de l'option choisie en paramètrage du plugin, récupérer les emails des destinataires
	$tous = array();
	$qui = lire_config('topics/notification/sujet_qui');
	switch ($qui) {
		case 'webmestres':
			$where = "webmestre='oui' AND statut!='poubelle'";
			break;
		case 'admins':
			$where = "statut='0minirezo'";
			break;
		case 'redacteurs':
			$where = "statut IN ('0minirezo', '1comite')";
			break;
		case 'visiteurs':
			$where = "statut IN ('0minirezo', '1comite', '6forum')";
			break;
		case 'webmaster':
		default:
			$email_webmaster = lire_config('email_webmaster');
			$where = "email=".sql_quote($email_webmaster);
			break;
	}
	
	$res = sql_allfetsel('email', 'spip_auteurs', $where);
	$tous = array_column($res, 'email');

	$destinataires = pipeline('notifications_destinataires',
		array(
			'args' => array('quoi' => $quoi, 'id' => $id_topic, 'options' => $options),
			'data' => $tous
		)
	);

	// Nettoyer le tableau des destinataires
	// pas d'exclusion ici : on envoi la notification également à l'auteur·e 
	notifications_nettoyer_emails($destinataires);

	// Sujet du mail : récupérer le titre du sujet
	$titre = sql_getfetsel('titre', 'spip_topics', 'id_topic='.intval($id_topic));
	$nom_site = lire_config('nom_site');
	$sujet  = 'Forum '.$nom_site.' : '.$titre;

	// Email_from : c'est l'adresse email enregistrée dans la configuration, sinon, par défaut c'est l'email de l'auteur·e du sujet
	$email_from = lire_config('topics/notification/email_from');
	if (
		!$email_from 
		or strlen($email_from) == 0
	) {
		if ($id_auteur 	= sql_getfetsel('id_auteur', 'spip_auteurs_liens', 'objet='.sql_quote('topic').' AND id_objet='.intval($id_topic))) {
			$email_from = sql_getfetsel('email', 'spip_auteurs', 'id_auteur='.intval($id_auteur));
		}
	}

	// hop, on envoi
	if (
		count($destinataires) > 0 
		and $email_from
	) {
		$modele = 'notifications/sujet_forum';
		$texte = email_notification_objet($id_topic, 'topic', $modele);
		notifications_envoyer_mails($destinataires, $texte, $sujet, $email_from);

		// La fonction notifications_envoyer_mails() ajoute les notifs dans la file des travaux (voir https://www.spip.net/fr_article5527.html)
		// Du coup, sur un site à faible audience, il peut se passer un certain temps avant que les premières notifs ne soient envoyées.
		// Si le plugin Accélerer Jobs est présent, on pousse immédiatement les 50 premières notifs.
		if (test_plugin_actif('accelerer_jobs')) {
			$accelerer_jobs = charger_fonction('accelerer_jobs','action');
			$accelerer_jobs('envoyer_mail/50');
		}
	}
}
