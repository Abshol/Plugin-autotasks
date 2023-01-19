<?php
include ("../inc/config.class.php");
include ("../../../inc/includes.php");

if (!defined('GLPI_ROOT')) {
   echo "Vous n'avez pas l'authorisation d'accéder à cette page";
} 
Session::checkRight("config", UPDATE);

// To be available when plugin in not activated
Plugin::load('autotasks');

Html::header("AutoTasks Config", $_SERVER['PHP_SELF'], "config", "plugins");
echo __("<h2>Ici vous pouvez forcer l'activation de la tâche automatique du plugin soit sur les dernières 24h, soit sur toute la base (Recommandé uniquement en cas d'urgence pour les grosses bases de données)</h2> </br>", 'autotasks');
echo "<form method='GET' action=''><div class='container'>";
echo "<span class='firstbutton'><input type='submit' name='reload' value='Recharger les dernières 24h'></span>";
echo "<span class='secondbutton'><input type='submit' name='hardreload' value='Recharger TOUTE la base de données'></span></div></form>";


if (isset($_GET['reload'])) {
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
   $autotsk = new pluginautotasksAutoTasks();
   if ($autotsk->starttask($sql)) {
      echo "<span style='color:green;'>L'action a été réalisée avec succès</span>";
   } else {
      echo "<span style='color:red;'>Une erreur s'est produite lors du rechargement de la base</span>";
   }
}
if (isset($_GET['hardreload'])) {
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE state = 2";
   $autotsk = new pluginautotasksAutoTasks();
   if ($autotsk->starttask($sql)) {
      echo "<span style='color:green;'>L'action a été réalisée avec succès</span>";
   } else {
      echo "<span style='color:red;'>Une erreur s'est produite lors du rechargement de la base</span>";
   }
}

Html::footer();