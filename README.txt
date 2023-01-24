Un plug-in crée pendant un stage pour aller avec GLPI

Ce plug-in va, toutes les 5 minutes (par défaut), parcourir la base de données à la recherche de tâches de tickets ayant été cochées comme fini dans les dernière 24h.
S'il en trouve, il va passer la tâche suivante de l'état "information" à "à faire" sauf si celle-ci a déjà été faite.
Enfin il passera l'attribution du ticket au groupe affilié à la tâche suivante.

Une page web est disponible avec le plug-in permettant de forcer la tâche à s'activer sans passer par le cron.
Il y est aussi possible de faire parcourir le plug-in dans toute la base de données (et pas seulement dans les données modifiées dans les dernières 24h),
cette dernière action n'est recommandée que lors de l'installation du plug-in ou en cas d'urgence car elle peut prendre du temps dans les grosses bases de données. (Il est par ailleurs impossible de le faire plus d'une fois par jour)

-------------------------------

N'oubliez pas de faire un "composer install" pour installer les librairies requises

N'utilisez le fichier web.php qu'en cas de tests !