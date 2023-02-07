INFORMATIONS

Un plug-in crée pendant un stage pour accompagner GLPI dans la gestion des tickets et de l'escalation des tâches

Ce plug-in va, toutes les 5 minutes (par défaut), parcourir la base de données à la recherche de tâches de tickets ayant été cochées comme "fini" (state=2 dans la table glpi_tickets) dans les dernières 24h.
S'il en trouve, il va passer la tâche suivante de l'état "information" (state = 0) à "à faire" (state = 1) sauf si celle-ci a déjà été faite.

Enfin il s'occupera de l'escalation des tâches selon plusieurs cas:
    -Si le ticket n'a pas de groupe affilié, il va créer une affiliation dans la table glpi_groups_tickets
    -Si le ticket est déjà affilié à un groupe, il va remplacer cette affiliation avec celle du nouveau groupe
    -Si le ticket a plusieurs groupes affiliés, il va supprimer le groupe qui a fini sa tâche

Le fichier config.form.php est une page web permettant de forcer la tâche à s'activer sans passer par le cron.
Il y est aussi possible de faire parcourir le plug-in dans toute la base de données (et pas seulement dans les données modifiées dans les dernières 24h),
cette dernière action n'est recommandée que lors de l'installation du plug-in ou en cas d'urgence car elle peut prendre du temps dans les grosses bases de données. (Il est par ailleurs impossible de le faire plus d'une fois par jour)

Il y est possible de faire plusieurs choses:

Activer la page web.php ayant la même utilitée que le fichier config.form.php, mais en étant complétement à part de glpi, permettant de vérifier si les erreurs que vous avez viennent de votre glpi, ou d'ailleurs'.
Activer un formulaire de création de tickets
Modifier le nombre d'action sur toute la base de données (appelée "hard-reset") possible par jours

------------------------------

BASE DE DONNÉES

Le plug-in crée plusieurs tables dans la base de données pour son bon fonctionnement:

- glpi_plugin_autotaskslogs:
    Cette table va logger tout les refresh effectués manuellement, renseignant l'id de l'utilisateur, si c'était une action sur toute la base de données (hardreset = 1) ou juste sur les dernières 24 heures (hardreset = 0), la date de celui-ci, et si l'action à échouée (success = 0) ou non (success = 1)

- glpi_plugin_autotasksconf:
    Cette table sert de configuration, elle permet d'activer et de désactiver les pages web.php et le formulaire de création de tickets par l'intermédiaire de la page config.form.php

- glpi_plugin_autotaskslogs_changeconf:
    Cette table va logger toutes actions qui va tenter de changer la configuration du plug-in

**
Les tables servant à logger auront leurs données automatiquement supprimées si celles-ci dépassent sont renseignées depuis plus de 6 mois conformément aux lois de protections des données
**

------------------------------

INSTALLATION

N'oubliez pas de faire un "composer install" pour installer les librairies requises

Il est nécessaire que votre utilisateur web ait les droits d'accès sur le fichier tools/history.log afin de remplir les logs et de pouvoir lancer manuellement le plug-in

N'utilisez le fichier web.php qu'en cas de tests !

------------------------------

TO DO

Table de logs pour les changements de configurations - DONE