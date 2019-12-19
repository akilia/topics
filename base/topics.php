<?php
/**
 * Déclarations relatives à la base de données
 *
 * @plugin     Sujets de Forum
 * @copyright  2019
 * @author     Pierre Miquel
 * @licence    GNU/GPL
 * @package    SPIP\Topics\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


/**
 * Déclaration des alias de tables et filtres automatiques de champs
 *
 * @pipeline declarer_tables_interfaces
 * @param array $interfaces
 *     Déclarations d'interface pour le compilateur
 * @return array
 *     Déclarations d'interface pour le compilateur
 */
function topics_declarer_tables_interfaces($interfaces) {

	$interfaces['table_des_tables']['topics'] = 'topics';

	return $interfaces;
}


/**
 * Déclaration des objets éditoriaux
 *
 * @pipeline declarer_tables_objets_sql
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function topics_declarer_tables_objets_sql($tables) {

	$tables['spip_topics'] = array(
		'type' => 'topic',
		'principale' => 'oui',
		'field'=> array(
			'id_topic'           => 'bigint(21) NOT NULL',
			'id_rubrique'        => 'bigint(21) NOT NULL DEFAULT 0',
			'id_secteur'         => 'bigint(21) NOT NULL DEFAULT 0',
			'titre'              => 'text NOT NULL DEFAULT ""',
			'texte'              => 'text NOT NULL DEFAULT ""',
			'date'               => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'statut'             => 'varchar(20)  DEFAULT "0" NOT NULL',
			'maj'                => 'TIMESTAMP'
		),
		'key' => array(
			'PRIMARY KEY'        => 'id_topic',
			'KEY id_rubrique'    => 'id_rubrique',
			'KEY id_secteur'     => 'id_secteur',
			'KEY statut'         => 'statut',
		),
		'titre' => 'titre AS titre, "" AS lang',
		'date' => 'date',
		'champs_editables'  => array('titre', 'texte', 'id_rubrique', 'id_secteur'),
		'champs_versionnes' => array('id_rubrique', 'id_secteur'),
		'rechercher_champs' => array("titre" => 8, "texte" => 5),
		'tables_jointures'  => array(),
		'statut_textes_instituer' => array(
			'prepa'    => 'texte_statut_en_cours_redaction',
			'publie'   => 'texte_statut_publie',
			'refuse'   => 'texte_statut_refuse',
			'poubelle' => 'texte_statut_poubelle',
		),
		'statut'=> array(
			array(
				'champ'     => 'statut',
				'publie'    => 'publie',
				'previsu'   => 'publie,prop,prepa',
				'post_date' => 'date',
				'exception' => array('statut','tout')
			)
		),
		'texte_changer_statut' => 'topic:texte_changer_statut_topic',


	);

	return $tables;
}

/**
 * Déclaration des tables secondaires
 *
 * @pipeline declarer_tables_auxiliaires
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function topics_declarer_tables_auxiliaires($tables) {

	$tables['spip_notifsubscribtions'] = array(
		'field' => array(
			'id_notifsubscriber' => 'bigint(21) AUTO_INCREMENT NOT NULL',
			'id_auteur'          => 'bigint(21) DEFAULT "0" NOT NULL',
			'id_objet'           => 'bigint(21) DEFAULT "0" NOT NULL',
			'objet'              => 'varchar(25) DEFAULT "" NOT NULL',
			'date'               => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'actif'          	 => 'varchar(3) DEFAULT "non" NOT NULL', // [non, oui] : par défaut l'auteur est désabonné.
			'maj'                => 'TIMESTAMP'
		),
		'key' => array(
			'PRIMARY KEY'        => 'id_notifsubscriber, id_auteur, id_objet, objet',
			'KEY id_notifsubscriber' => 'id_notifsubscriber',
			'KEY id_auteur'      => 'id_auteur',
			'KEY id_objet'       => 'id_objet',
			'KEY objet'          => 'objet',
		)
	);

	return $tables;
}
