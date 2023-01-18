<?php
include ("../inc/autotasks.class.php");
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
Html::footer();

if (isset($_GET['reload'])) {
   // $autotsk = new autotasks();
   // $autotsk->cronAutoTasks();
   global $DB;
      $cron_status = 0;
      $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
      if ($result = $DB->query($sql) or die($DB->error)) {

         if ($DB->numrows($result) > 0) { //Vérification si le Select a bien récupéré des données, si non, la procédure ne se déclenche pas

            while ($row = $DB->fetch_assoc($result)) {
               $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, state, tickets_id, content FROM glpi_tickettasks WHERE tickets_id = " . $row['tickets_id'];

               if ($resultset = $DB->query($sql) or die($DB->error)) {

                  $before = false; //Cette variable sert à déterminer si le state de la tâche précédente est à 2 (true) ou non (false)

                  foreach ($rows = $DB->fetch_assoc($resultset) as $key => $value) {
                     if ($value['state'] != 2) {
                        if ($before = true) { //Si la précédente tâche est passée à 2, on passe celle-ci à 1
                           $sql = "UPDATE glpi_tickettasks SET state = 1 WHERE id = " . $value['id'] . ";";
                           if ($result = $DB->query($sql) or die($DB->error)) {
                              $cron_status = 1;
                           } else {
                              return $cron_status;
                           }
                           break;
                        }
                        $before = false;
                     } else {
                        $before = true;
                        $lastRank = $row['row'];
                     }
                  }
               }
            }
         }
         echo $cron_status == true ? "Reussite" : "Echec";
      }
}