<?php

include ("../inc/config.class.php");
include ("../../../inc/includes.php");
if (!defined('GLPI_ROOT')) {
   echo "Vous n'avez pas l'authorisation d'accéder à cette page";
} 
Session::checkRight("config", UPDATE); 
$style = '';
if (isset($_GET['hardreload']) && !isset($_GET['verif'])) {
   $style = "color:red;";
}


global $DB;

// To be available when plugin in not activated
Plugin::load('autotasks');
Html::header("AutoTasks Config", $_SERVER['PHP_SELF'], "config", "plugins");
echo __("<h2>Ici vous pouvez forcer l'activation de la tâche automatique du plugin soit sur les dernières 24h, soit sur toute la base (Recommandé uniquement en cas d'urgence pour les grosses bases de données)</h2> </br>", 'autotasks');
echo __("<form method='GET' action=''><div class='container'>", 'autotasks');
echo __("<span class='firstbutton'><input type='submit' name='reset' value='Recharger les dernières 24h'></span>", 'autotasks');
echo __("<span class='secondbutton'><input type='submit' name='hardreset' value='Recharger TOUTE la base de données'>", 'autotasks');
echo __("<input type='checkbox' name='verif' id='verif' value='true'><label for='verif' style='$style'>Cochez cette case si vous êtes sur de vouloir recharger toute la base  </label></span></div></form>", 'autotasks');

if (isset($_GET['reset'])) {
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
   $autotsk = new pluginautotasksAutoTasks();
   $reussite = $autotsk->starttask($sql);
   if ($autotsk->tasklog($reussite, $DB)) {
      echo "<span style='color:green;'>L'action a été réalisée avec succès</span>";
   } else {
      echo "<span style='color:red;'>Une erreur s'est produite lors du rechargement de la base</span>";
   }
}
if (isset($_GET['hardreset'])&& isset($_GET['verif'])) {
   $autotsk = new pluginautotasksAutoTasks();
   if ($autotsk->hardreset(Session::getLoginUserID(), $DB)) {
      echo "<span style='color:red;'>Erreur: Vous avez déjà effectué cette action dans la journée</span>";
   } else {
      $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE state = 2";
      $reussite = $autotsk->starttask($sql);
      if ($autotsk->tasklog($reussite, $DB, true)) {
         echo "<span style='color:green;'>L'action a été réalisée avec succès</span>";
      } else {
         echo "<span style='color:red;'>Une erreur s'est produite lors du rechargement de la base</span>";
      }
   }
}

Html::footer();