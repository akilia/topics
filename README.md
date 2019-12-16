# Plugin Topics pour SPIP
Dans un forum, proposer des sujets

## Objectif
Avoir un objet éditorial dédié à la création de sujet pour un Forum

## Pourquoi ce besoin ?
La gestion de Forums privés et publics est native dans SPIP grâce à la présence d'un plugin appelé Forum, livré dans le paquet /plugins-dist.

En fait, ce plugin Forum aurait dû s'appeler *Commentaires* puisqu'il permet essentiellement de **gérer les commentaires** (ou messages, c'est selon) attachés à n'importe quel objets éditorial de SPIP (et pas seulement les articles !).


Le plugin Topics propose un objet éditorial dédié à la création de sujets, des notifications dédiés, une gestion dans le back-office dédiée et enfin des squelettes pour une utilisation dans l'espace publique.

Cela apporte qqs avantages
* Une configuration des notications liés à la création de sujets
* Tableau liste des sujets avec infos dédiées (colonne nbre de réponse, etc.)
* Gestion dédié des fichiers attachés;
* possibilités d'avoir des stats dédiés (plugin Stat Objets);
* fournir des squelettes dédiés;
* etc.

## Prérequis et Configuration

### Prérequis
SPIP >= 3.2

**Les plugins pris en compte automatiquement à l'installation**
* [Saisies]()
* [Facteur]()

**D'autres sont recommandés**
* [Comments](https://contrib.spip.net/Comment-3-pour-SPIP-3) : il permet entre autres de choisir un affichage des commentaires en liste à plat, en enfilade (thread), ou en enfilade à un seul niveau.
* [Notifications](https://contrib.spip.net/Notifications) : il permet entre autres une gestion fine des notifications des forums publics.
> Note importante : avec la combinaison de Comments et Notifications, le formulaire de commentaire #FORMULAIRE_FORUM de SPIP propose de s’abonner ou non à la discussion par courriel, via une case à cocher.

* [Nospam](https://contrib.spip.net/NoSPAM) : pour limiter les SPAMs, comme son nom l’indique, sans emmerder les internautes par un captcha.
* [Magnet](https://contrib.spip.net/magnet) : pour par exemple mettre en avant un ou des sujets.



### Configuration
Il est possible de configurer des Notifications.
Pour l’annonce des nouveaux sujets par exemple.
