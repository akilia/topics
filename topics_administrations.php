<?php
/**
 * Fichier gérant l'installation et désinstallation du plugin Sujets de Forum
 *
 * @plugin     Sujets de Forum
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Topics\Installation
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Fonction d'installation et de mise à jour du plugin Sujets de Forum.
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @param string $version_cible
 *     Version du schéma de données dans ce plugin (déclaré dans paquet.xml)
 * @return void
**/
function topics_upgrade($nom_meta_base_version, $version_cible) {
	$maj = array();

	$maj['create'] = array(array('maj_tables', array('spip_topics')));

	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}


/**
 * Fonction de désinstallation du plugin Sujets de Forum.
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @return void
**/
function topics_vider_tables($nom_meta_base_version) {

	sql_drop_table('spip_topics');

	# Nettoyer les liens courants (le génie optimiser_base_disparus se chargera de nettoyer toutes les tables de liens)
	sql_delete('spip_documents_liens', sql_in('objet', array('topic')));
	sql_delete('spip_mots_liens', sql_in('objet', array('topic')));
	sql_delete('spip_auteurs_liens', sql_in('objet', array('topic')));
	# Nettoyer les versionnages et forums
	sql_delete('spip_versions', sql_in('objet', array('topic')));
	sql_delete('spip_versions_fragments', sql_in('objet', array('topic')));
	sql_delete('spip_forum', sql_in('objet', array('topic')));

	effacer_meta('topics');
	effacer_meta($nom_meta_base_version);
}
