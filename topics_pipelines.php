<?php
/**
 * Utilisations de pipelines par Sujets de Forum
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
 * Ajouter les objets sur les vues des parents directs
 *
 * @pipeline affiche_enfants
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
**/
function topics_affiche_enfants($flux) {
	if ($e = trouver_objet_exec($flux['args']['exec']) and $e['edition'] == false) {
		$id_objet = $flux['args']['id_objet'];

		if ($e['type'] == 'rubrique') {

			 if (test_plugin_actif('magnet')) {
			 	$flux['data'] .= recuperer_fond(
					'prive/objets/liste/topics-magnet',
					array(
						'titre' => _T('topic:titre_topics_rubrique'),
						'id_rubrique' => $id_objet
					)
				);
			 }

			$flux['data'] .= recuperer_fond(
				'prive/objets/liste/topics',
				array(
					'titre' => _T('topic:titre_topics_rubrique'),
					'id_rubrique' => $id_objet
				)
			);

			if (autoriser('creertopicdans', 'rubrique', $id_objet)) {
				include_spip('inc/presentation');
				$flux['data'] .= icone_verticale(
					_T('topic:icone_creer_topic'),
					generer_url_ecrire('topic_edit', "id_rubrique=$id_objet"),
					'topic-24.png',
					'new',
					'right'
				) . "<br class='nettoyeur' />";
			}

		}
	}
	return $flux;
}

/**
 * Ajout de contenu sur certaines pages,
 * notamment des formulaires de liaisons entre objets
 *
 * @pipeline affiche_milieu
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function topics_affiche_milieu($flux) {
	$texte = '';
	$e = trouver_objet_exec($flux['args']['exec']);

	// auteurs sur les topic
	if (!$e['edition'] and in_array($e['type'], array('topic'))) {
		$texte .= recuperer_fond('prive/objets/editer/liens', array(
			'table_source' => 'auteurs',
			'objet' => $e['type'],
			'id_objet' => $flux['args'][$e['id_table_objet']]
		));
	}



	if ($texte) {
		if ($p = strpos($flux['data'], '<!--affiche_milieu-->')) {
			$flux['data'] = substr_replace($flux['data'], $texte, $p, 0);
		} else {
			$flux['data'] .= $texte;
		}
	}

	return $flux;
}

/**
 * Ajout de liste sur la vue d'un auteur
 *
 * @pipeline affiche_auteurs_interventions
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function topics_affiche_auteurs_interventions($flux) {
	if ($id_auteur = intval($flux['args']['id_auteur'])) {
		$flux['data'] .= recuperer_fond('prive/objets/liste/topics', array(
			'id_auteur' => $id_auteur,
			'titre' => _T('topic:info_topics_auteur')
		), array('ajax' => true));
	}
	return $flux;
}

/**
 * Afficher le nombre d'éléments dans les parents
 *
 * @pipeline boite_infos
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
**/
function topics_boite_infos($flux) {
	if (isset($flux['args']['type']) and isset($flux['args']['id']) and $id = intval($flux['args']['id'])) {
		$texte = '';
		if ($flux['args']['type'] == 'rubrique' and $nb = sql_countsel('spip_topics', array("statut='publie'", 'id_rubrique=' . $id))) {
			$texte .= '<div>' . singulier_ou_pluriel($nb, 'topic:info_1_topic', 'topic:info_nb_topics') . "</div>\n";
		}
		if ($texte and $p = strpos($flux['data'], '<!--nb_elements-->')) {
			$flux['data'] = substr_replace($flux['data'], $texte, $p, 0);
		}
	}
	return $flux;
}


/**
 * Compter les enfants d'un objet
 *
 * @pipeline objets_compte_enfants
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
**/
function topics_objet_compte_enfants($flux) {
	if ($flux['args']['objet'] == 'rubrique' and $id_rubrique = intval($flux['args']['id_objet'])) {
		// juste les publiés ?
		if (array_key_exists('statut', $flux['args']) and ($flux['args']['statut'] == 'publie')) {
			$flux['data']['topics'] = sql_countsel('spip_topics', 'id_rubrique= ' . intval($id_rubrique) . " AND (statut = 'publie')");
		} else {
			$flux['data']['topics'] = sql_countsel('spip_topics', 'id_rubrique= ' . intval($id_rubrique) . " AND (statut <> 'poubelle')");
		}
	}

	return $flux;
}



/**
 * Optimiser la base de données
 *
 * Supprime les objets à la poubelle.
 *
 * @pipeline optimiser_base_disparus
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function topics_optimiser_base_disparus($flux) {

	sql_delete('spip_topics', "statut='poubelle' AND maj < " . $flux['args']['date']);

	return $flux;
}

/**
 * Synchroniser la valeur de id secteur
 *
 * @pipeline trig_propager_les_secteurs
 * @param  string $flux Données du pipeline
 * @return string       Données du pipeline
**/
function topics_trig_propager_les_secteurs($flux) {

	// synchroniser spip_topics
	$r = sql_select(
		'A.id_topic AS id, R.id_secteur AS secteur',
		'spip_topics AS A, spip_rubriques AS R',
		'A.id_rubrique = R.id_rubrique AND A.id_secteur <> R.id_secteur'
	);
	while ($row = sql_fetch($r)) {
		sql_update('spip_topics', array('id_secteur' => $row['secteur']), 'id_topic=' . $row['id']);
	}

	return $flux;
}

/**
 * Par défaut un nouveau sujet a tout de suite le statut 'publié'
 *
 * @pipeline objets_compte_enfants
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
**/
function topics_pre_insertion($flux) {
    if ($flux['args']['table'] == 'spip_topics') {
        $flux['data']['statut'] = 'publie';
    }
    return $flux;
}

/**
 * Notifier si un nouveau sujet est créé 
 * note : on est sür que le sujet a déjà le statut "publie" grâce au traitement fait dans le pipeline topics_pre_insertion()
 */
function topics_formulaire_traiter($flux) {
	if (
		$flux['args']['form'] == 'editer_topic'
		AND !is_numeric($flux['args']['args']['0']) // c'est bien une création de sujet
	) { 
		$options = lire_config('topics/notification/sujet_qui');
		$id_topic = $flux['data']['id_topic'];
		if ($notifications = charger_fonction('notifications', 'inc')) {
			$notifications('nouveausujet', $id_topic, $options);
		}

		// on en profite pour personnaliser le message de retour du formulaire
		$flux['data']['message_ok'] = _T('topic:confirmer_creation_topic');
	}
	return $flux;
}

/**
 * Pour les commentaires, qui notifier ?
 * Enregistrer dans le journal 'topic.log' le type de notification et la liste des destinataires
 *
 * @pipeline notifications_destinataires
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function topics_notifications_destinataires($flux) {
	
	include_spip('notifications','inc');
	$quoi = $flux['args']['quoi'];
	$id   = $flux['args']['id'];

	// Commentaires : notifier tous les auteurs (admins et rédacteurs) ?
	if (
		$quoi == 'forumposte'
		and lire_config('topics/notification/commentaire_qui') == 'tous'
	) {
		$res = sql_allfetsel('email', 'spip_auteurs', "statut IN ('0minirezo', '1comite')");
		$liste_totale = array_column($res, 'email');

		/* Exclure les auteurs qui se sont désabonnés la notification en cours */
		/* Exemple : quels sont les id_auteur qui se sont désabonnés des commentaires sur le sujet id_topic = 6  ? */

		// A quel objet se rattache cette notification ?
		$objet = $flux['args']['options']['forum']['objet'];	// article, topic, etc.
		$id_objet = $flux['args']['options']['forum']['id_objet'];

		// Récupérer la liste des id_auteur qui se sont désabonné de cette notification
		$exclus = array();
		$e = sql_allfetsel('id_auteur', 'spip_notifsubscribtions', 'objet='.sql_quote($objet).' AND id_objet='.intval($id_objet).' AND actif='.sql_quote('non'));
		$e = array_column($e, 'id_auteur');
		foreach ($e as $value) {
			$exclus[] = sql_getfetsel('email', 'spip_auteurs', 'id_auteur='.$value); 
		}

		$flux['data'] = array_diff($liste_totale, $exclus);

	}

	// Loger la liste des destinataires qui doivent recevoir une notification
	// forumposte = les moderateurs. Au moins l'auteur du sujet
	// forumvalide = la liste des gens qui ont coché la case 'Prévenez-moi de toutes les nouvelles réponses de cette discussion par email'
	if (in_array($quoi, array('forumposte', 'nouveausujet'))) {
		$destinataires = $flux['data'];
		notifications_nettoyer_emails($destinataires);
		$liste_destinataires = implode(',', $destinataires);
		spip_log($quoi.', id='.$id.', '.$liste_destinataires, 'topic.' . _LOG_INFO_IMPORTANTE);
	}

	return $flux;
}

/**
 * Compatibilité avec plugin Compositions
 * Déclaration de l'héritage avec les rubriques
 *
 * @pipeline compositions_declarer_heritage
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function topics_compositions_declarer_heritage($heritages) {
	$heritages['topic'] = 'rubrique';
	return $heritages;
}

/**
 * Compatibilité avec plugin AccesRestrein 3.0
 * Ajouter Topic à la liste des objets restreints
 *
 * @pipeline accesrestreint_liste_objets_exclus
 * @param  array $objets Données du pipeline
 * @return array       Données du pipeline
 */
function topics_accesrestreint_liste_objets_exclus($objets) {
	return $objets;
}
