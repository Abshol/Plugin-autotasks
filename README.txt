INFORMATIONS

Un plug-in crée pendant un stage pour aller avec GLPI

Ce plug-in va, toutes les 5 minutes (par défaut), parcourir la base de données à la recherche de tâches de tickets ayant été cochées comme "fini" (state=2 dans la table glpi_tickets) dans les dernières 24h.
S'il en trouve, il va passer la tâche suivante de l'état "information" (state = 0) à "à faire" (state = 1) sauf si celle-ci a déjà été faite.

Enfin il s'occupera de l'escalation des tâches selon plusieurs cas:
    -Si le ticket n'a pas de groupe affilié, il va créer une affiliation dans la table glpi_groups_tickets
    -Si le ticket est déjà affilié à un groupe, il va remplacer cette affiliation avec celle du nouveau groupe
    -Si le ticket a plusieurs groupes affiliés, il va supprimer le groupe qui a fini sa tâche

Une page web est disponible avec le plug-in permettant de forcer la tâche à s'activer sans passer par le cron.
Il y est aussi possible de faire parcourir le plug-in dans toute la base de données (et pas seulement dans les données modifiées dans les dernières 24h),
cette dernière action n'est recommandée que lors de l'installation du plug-in ou en cas d'urgence car elle peut prendre du temps dans les grosses bases de données. (Il est par ailleurs impossible de le faire plus d'une fois par jour)

Le fichier web.php a la même utilitée, cependant il est complétement à part de glpi, permettant de vérifier si les erreurs que vous avez viennent de votre glpi, ou de votre base de données. Pour y accéder, supprimez le contenu du fichier .htaccess

------------------------------

Un formulaire de création de tickets est fourni avec le plug-in, si vous souhaitez le supprimer, supprimez le dossier "Form" dans le dossier "front" et enlevez la ligne 29 du fichier "config.form.php" dans le dossier "front"

-------------------------------

INSTALLATION

N'oubliez pas de faire un "composer install" pour installer les librairies requises

Il est nécessaire que votre utilisateur web ait les droits d'accès sur le fichier tools/history.log afin de remplir les logs et de pouvoir lancer manuellement le plug-in

N'utilisez le fichier web.php qu'en cas de tests !