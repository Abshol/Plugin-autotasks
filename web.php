<?php
/* 
* BE CAREFULL NOT TO LET THIS FILE ACCESSIBLE
* YOU MUST ONLY USE IT WHEN DEBUGGING
*
* This file's purpose is to help you debug by giving a less "glpi" verbose about what's the plugging doing, allowing you to test it on a different page disconnected to glpi's site
* 
* Remove the content of the .htaccess file to access this file
*
*
*
* Instanciation de Monolog qui permettra de gérer les logs php
*
*/

require_once("vendor/autoload.php");
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('transactions');
$logstream = new StreamHandler('tools/error.log');
$logger->pushHandler($logstream);

echo "<h2>Ici vous pouvez forcer l'activation de la tâche automatique du plugin soit sur les dernières 24h, soit sur toute la base (Recommandé uniquement en cas d'urgence pour les grosses bases de données)</h2> </br>";
echo "<form method='GET' action=''><div class='container'>";
echo "<span class='firstbutton'><input type='submit' name='reload' value='Recharger les dernières 24h'></span>";
echo "<span class='secondbutton'><input type='submit' name='hardreload' value='Recharger TOUTE la base de données'>";
echo "<input type='checkbox' name='verif' id='verif' value='true'><label for='verif'>Cochez cette case si vous êtes sur de vouloir recharger toute la base  </label></span></div></form>";

if (isset($_GET['hardreload']) && isset($_GET['verif'])) {
   $DB = new mysqli("localhost", "root", "root", "glpi");
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE state = 2";
   starttask($sql, $DB, $logger);
}
else if (isset($_GET['hardreload'])) {
   echo "<span style='color:red;'>Merci de cocher la case</span>";
}
if (isset($_GET['reload'])) {
   $DB = new mysqli("localhost", "root", "root", "glpi");
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
   starttask($sql, $DB, $logger);
}

function starttask ($sql, $DB, $logger) {
   if ($result = $DB->query($sql)) {
      var_dump($result = $DB->query($sql));
      if ($result->num_rows == 1) {
         if ($row = $result->fetch_assoc()) {
            echo task($row, $DB, $logger);
         } else {
            echo "<span style='color:red;'>Une erreur est survenue lors du traitement de la requête</span>";
            $logger->info("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
         }
      }
      else if ($result->num_rows > 1){
         while ($row = $result->fetch_assoc()) {
            $mess = task($row, $DB, $logger);
         }
         echo $mess;
      }
   } else {
      echo "<span style='color:red;'>Une erreur est survenue lors du traitement de la requête</span>";
      $logger->info("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
   }
}
function task ($row, $DB, $logger){
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, `id`, `state`, tickets_id, content FROM glpi_tickettasks WHERE tickets_id = " . $row['tickets_id'];
   $success = true;
   if ($resultset = $DB->query($sql)) {
      $before = false; //Cette variable sert à déterminer si le state de la tâche précédente est à 2 (true) ou non (false)
      $break = false;
      while ($rows = $resultset->fetch_assoc()) {
         if ($rows['state'] != "2") {
            if ($before = true) { //Si la précédente tâche est passée à 2, on passe celle-ci à 1
               $sql = "UPDATE glpi_tickettasks SET state = 1 WHERE id = " . $rows['id'] . ";";
               if ($resultset = $DB->query($sql)) {
                  $success = true;
               } else {
                  $success = false;
               }
               $break = true;
            }
            $before = false;
         } else {
            $before = true;
         }
         if ($break) {break;}
      }
   } else {
      $logger->info("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
      return "<span style='color:red;'>Une erreur est survenue lors du traitement de la requête</span>";
   }
   if ($success) {
      $logger->info("Rechargement de la base effectué avec succès".$DB->error);
      return "<span style='color:green;'>L'action a été réalisée avec succès</span>";
   }
   else {
      $logger->info("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
      return "<span style='color:red;'>Une erreur est survenue lors du traitement de la requête</span>";
   }
}