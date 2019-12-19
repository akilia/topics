<?php
/**
 * Fonctions utiles au plugin Sujets de Forum
 *
 * @plugin     Sujets de Forum
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Topics\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Retrouver, pour l'auteur connecté son id_notifsubscriber pour tel objet/id_objet
 * 
 * @param int $id_auteur 
 * @param int $id_objet 
 * @param string $objet  (ex. : article, topic)
 * @return int  l'id_notifsubscriber
**/ 
function topic_get_id_notifsubscriber($id_auteur, $id_objet, $objet) {
	$îd_notifsubscriber = sql_getfetsel('id_notifsubscriber', 'spip_notifsubscribtions',  'id_auteur='.intval($id_auteur).' and id_objet='.intval($id_objet).' and objet='.sql_quote($objet));

	return $îd_notifsubscriber;
}
