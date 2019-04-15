<?php
/**
 * Gestion du formulaire de configuration de topic
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

function formulaires_configurer_topics_verifier() {

	$erreurs = array();

	// Vérifier que l'adresse email, si renseignée, est bien valide
	$email_from = _request('email_from');
	if (strlen($email_from) > 0 and !email_valide($email_from)) {
		$erreurs['email_from'] = _T('form_email_non_valide');
	}

	return $erreurs;
}