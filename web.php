<?php
/**
 * NE LAISSEZ PAS CE FICHIER ACCESSIBLE SUR VOTRE SERVEUR
 * A N'UTILISER QU'EN CAS DE DEBUG
 *
 * Ce fichier est là pour vous aider en cas de bugs afin de vous donner une version moins "glpienne" des messages d'erreurs (le code ici est le même que dans config.form.php sans les variables et classes de glpi)
 * 
 * Supprimez le fichier .htaccess (ou juste le point devant) pour rendre ce fichier accessible
 *
 */

require_once("vendor/autoload.php");
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Changez ces variables avec les valeurs correspondants à votre db
 */
$host = 'host';
$user = 'user';
$pass = 'pass';
$database = "database";

$logger = new Logger('transactions');
$logstream = new StreamHandler('tools/error.log');
$logger->pushHandler($logstream);

echo "<div class='main_form rss card singleaction center-h' style='width:65%; margin:0% 25% 0% 15%;'><div class='ui-widget-header'><h2>Ici vous pouvez forcer l'activation de la tâche automatique du plugin soit sur les dernières 24h, soit sur toute la base (Recommandé uniquement en cas d'urgence pour les grosses bases de données)</h2></div></br>";
echo "<form method='GET' action=''>";
echo "<div class='rich_text_container'><span class='btn-linkstyled left'><input type='submit' class='vsubmit' name='reset' value='Recharger les dernières 24h'></span>";
echo "<span class='right'><input type='submit' class='vsubmit' name='hardreset' value='Recharger TOUTE la base de données'>";
echo "<input type='checkbox' name='verif' id='verif' value='true'><label for='verif'>Cochez cette case si vous êtes sur de vouloir recharger toute la base </label></span></div>";

if (isset($_GET['hardreset']) && isset($_GET['verif'])) {
   $DB = new mysqli($host, $user, $pass, $database);
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE state = 2";
   starttask($sql, $DB, $logger);
}
else if (isset($_GET['hardreset'])) {
   echo "<span style='color:red;'>Merci de cocher la case</span>";
}
if (isset($_GET['reset'])) {
   $DB = new mysqli("localhost", "root", "root", "glpi");
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
   starttask($sql, $DB, $logger);
}

function starttask ($sql, $DB, $logger) {
   $mess = false;
   if ($result = $DB->query($sql)) {
      if ($result->num_rows == 1) {
         if ($row = $result->fetch_assoc()) {
            return task($row, $DB, $logger);
         } else {
            $logger->info("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
         }
      } else if ($result->num_rows > 1){
         while ($row = $result->fetch_assoc()) {
            switch ($mess = task($row, $DB, $logger)) {
               case true:
                  $break = false;
                  break;
               case false:
                  $break = true;
                  break;
            }
            if ($break) {
               $logger->info("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
               break;
            }
         }
      } else {
         $logger->info("", false);
         return true;
      }
   } else {
      $logger->info("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
   }
   return $mess;
}
function task ($row, $DB, $logger) {
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, `id`, `state`, tickets_id, content FROM glpi_tickettasks WHERE tickets_id = ".$row['tickets_id'].";";
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
   }
   if (!$success) {
      $logger->info("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
   }
   return $success;
}
