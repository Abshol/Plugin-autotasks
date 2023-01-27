<?php
global $DB, $CFG_GLPI;
if(file_exists("../inc/includes.php")) {
   include("../inc/includes.php");
} else {
   include("../../../inc/includes.php");
}
include("../inc/config.class.php");

if (!defined('GLPI_ROOT')) {
   echo "Vous n'avez pas l'authorisation d'accéder à cette page";
} 
Session::checkRight("config", UPDATE); 
$style = '';
if (isset($_GET['hardreload']) && !isset($_GET['verif'])) {
   $style = "color:red;";
}

Plugin::load('autotasks');
Html::header("AutoTasks Config", $_SERVER['PHP_SELF'], "config", "plugins");
echo __("<div class='main_form rss card singleaction center-h' style='width:65%; margin:0% 25% 0% 15%;'><div class='ui-widget-header'><h2>Ici vous pouvez forcer l'activation de la tâche automatique du plugin soit sur les dernières 24h, soit sur toute la base (Recommandé uniquement en cas d'urgence pour les grosses bases de données)</h2></div></br>", 'autotasks');
echo __("<form method='GET' action=''>", 'autotasks');
echo __("<div class='rich_text_container'><span class='btn-linkstyled left'><input type='submit' class='vsubmit' name='reset' value='Recharger les dernières 24h'></span>", 'autotasks');
echo __("<span class='right'><input type='submit' class='vsubmit' name='hardreset' value='Recharger TOUTE la base de données'>", 'autotasks');
echo __("<input type='checkbox' name='verif' id='verif' value='true'><label for='verif' style='$style'>Cochez cette case si vous êtes sur de vouloir recharger toute la base </label></span></div>", 'autotasks');

if (isset($_GET['reset'])) {
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
   $autotsk = new PluginautotasksConfig();
   $reussite = $autotsk->starttask($sql);
   if ($autotsk->tasklog($reussite, $DB)) {
      echo "<br><br><span style='color:green;'>L'action a été réalisée avec succès</span>";
   } else {
      echo "<br><br><span style='color:red;'>Une erreur s'est produite lors du rechargement de la base</span>";
   }
}
if (isset($_GET['hardreset'])&& isset($_GET['verif'])) {
   $autotsk = new PluginautotasksConfig();
   if ($autotsk->hardreset(Session::getLoginUserID(), $DB)) {
      echo "<br><br><span style='color:red;'>Erreur: Vous avez déjà effectué cette action dans la journée</span>";
   } else {
      $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE state = 2";
      $reussite = $autotsk->starttask($sql);
      if ($autotsk->tasklog($reussite, $DB, true)) {
         echo "<br><br><span style='color:green;'>L'action a été réalisée avec succès</span>";
      } else {
         echo "<br><br><span style='color:red;'>Une erreur s'est produite lors du rechargement de la base</span>";
      }
   }
}
echo "<br><br></div></form>";
Html::footer();