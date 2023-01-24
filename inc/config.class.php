<?php
require_once("../vendor/autoload.php");
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class PluginautotasksConfig extends CommonDBTM
{
   /**
    * Fonction qui va préparer la tâche dans le cas où il y a plusieurs résultats, un seul ou aucun
    *
    * @param string $sql Requête SQL
    *
    * @return bool Si La tâche s'est effectuée sans problèmes
    */
   public function starttask ($sql) {
      global $DB, $CFG_GLPI;
      $mess = false;
      if ($result = $DB->query($sql)) {
         if ($DB->numrows($result) == 1) {
            if ($row = $DB->fetch_assoc($result)) {
               return $this->task($row, $DB);
            } else {
               $this->logs("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
            }
         } else if ($DB->numrows($result) > 1){
            while ($row = $DB->fetch_assoc($result)) {
               switch ($mess = $this->task($row, $DB)) {
                  case true:
                     $break = false;
                     break;
                  case false:
                     $break = true;
                     break;
               }
               if ($break) {
                  $this->logs("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
                  break;
               }
            }
         } else {
            $this->logs("", false);
            return true;
         }
      } else {
         $this->logs("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
      }
      return $mess;
   }

   /**
    * Lancement de la tâche
    *
    * @param mixed $row Lignes récupérées par la requête précédente
    * @param mysqli $DB Base de données
    * 
    * @return bool
    */
   function task ($row, $DB) {
      $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, `id`, `state`, tickets_id, groups_id_tech FROM glpi_tickettasks WHERE tickets_id = ".$row['tickets_id'].";";
      $success = true;
      if ($resultset = $DB->query($sql)) {
         $before = false; //Cette variable sert à déterminer si le state de la tâche précédente est à 2 (true) ou non (false)
         $break = false;
         while ($rows = $DB->fetch_assoc($resultset)) {
            if ($rows['state'] != "2") {
               if ($before = true) { //Si la précédente tâche est passée à 2, on passe celle-ci à 1
                  if ($this->groupVerif($rows, $DB)) {
                     $sql2 = "UPDATE glpi_tickettasks SET state = 1 WHERE id = " . $rows['id'] . ";";
                     if ($DB->query($sql2)) {
                        $success = $this->groups($rows, $DB);
                     } else {
                        $success = false;
                     }
                  } else {
                     $this->groupDelete($rows, $DB);
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
         $this->logs("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
      }
      if (!$success) {
         $this->logs("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
      }
      return $success;
   }

   /**
    * Cette fonction permet d'insérer les logs dans la base de données
    * 
    * @param boolean $success Pour savoir si la tâche a été exécutée avec succès 
    * @param mysqli $DB Base de données
    * @param boolean $hardreset Pour savoir si c'est toute la base de données qui a été reset (default = false)
    * 
    * @return boolean True si la tâche a été réalisée, false si non
    * 
    */
   function tasklog($success, $DB, $hardreset = false) {
      $sql = "INSERT INTO glpi_plugin_autotaskslogs (`user`, `hardreset`, `date`,success) VALUES (" . Session::getLoginUserID() . ", " . ($hardreset?"TRUE":"FALSE") . ", DATE(NOW()), " . ($success?"TRUE":"FALSE") . ");";
      $DB->query($sql);
      if ($success) {
         return true;
      }
      return false;
   }

   /**
    * Fonction vérifiant si plusieurs groupes sont affiliés au ticket, renvoie false si c'est le cas, true si non
    * 
    * @param mixed $row Lignes récupérées via la requête précédente 
    * @param mysqli $DB Base de données
    *
    * @return boolean
    */
   function groupVerif($row, $DB) {
      $sql = "SELECT COUNT(*) as `nombre` FROM glpi_groups_tickets WHERE tickets_id = " . $row['tickets_id'];
      $resultset = $DB->query($sql);
      $row = $DB->fetch_assoc($resultset);
      if ($row['nombre'] > 1) {
         return false;
      } else {
         return true;
      }
   }

   /**
    * Dans le cas où il y a plusieurs groupes, cette fonction va supprimer celui dont la tâche est terminée
    *
    * @param mixed $row Lignes récupérées via la requête précédente 
    * @param mysqli $DB Base de données
    * 
    * @return boolean
    */
   function groupDelete($row, $DB) {
      $sql = "DELETE FROM glpi_groups_tickets WHERE groups_id = ".$row['groups_id_tech']." AND tickets_id = ".$row['tickets_id'];
      if ($DB->query($sql)) {
         $this->logs("Suppression de l'attribution du groupe ".$row['groups_id_tech']." au ticket ".$row['tickets_id']." réussie");
         return true;
      } else {
         $this->logs("Echec de la suppression de l'attribution du groupe ".$row['groups_id_tech']." au ticket ".$row['tickets_id']);
         return false;
      }
   }
   /**
    * Fonction permettant d'attribuer le groupe de la tâche suivante au ticket et de supprimer celui qui était actuellement attribué
    * 
    * @param mixed row Lignes récupérées via la requête précédente
    * @param mysqli $DB Base de données 
    *
    * @return boolean Selon si cela s'est bien déroulé
    */
    function groups($row, $DB) {
      // Vérification
      // On change l'affiliation de ce ticket à ce groupe
      $sql = "UPDATE glpi_groups_tickets SET groups_id = " . $row['groups_id_tech'] . " WHERE tickets_id = " . $row['tickets_id'] . ";";
      // returns true or false depending on success
      if ($DB->query($sql)) {
         $this->logs("Attribution du groupe " . $row['groups_id_tech'] . " au ticket " . $row['tickets_id'] . " réussie");
         return true;
      } else {
         $this->logs("Echec de l'attribution du groupe " . $row['groups_id_tech'] . " au ticket " . $row['tickets_id']);
         return false;
      }
   }

   /**
    * Cette fonction permet de savoir si l'utilisateur connecté a déjà effectué un "hardreset" ou non, si oui, celui-ci ne se fera pas
    *
    * @param int $userid Id de l'utilisateur connecté
    * @param mysqli $DB Base de données
    *
    * @return boolean
    * 
    */
   function hardreset($userid, $DB) {
      $sql = "SELECT COUNT(*) AS user FROM glpi_plugin_autotaskslogs WHERE user = $userid AND `date` = DATE(NOW()) AND hardreset = 1;";
      if ($result = $DB->query($sql)) {
         $row = $DB->fetch_assoc($result);
         if ($row['user'] > 0) {
            return true;
         } else {
            return false;
         }
      } else {
         $this->logs("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
         return false;
      }
   }

   /**
    * Permet de logs les erreurs survenues lors des requêtes
    * 
    * @param mixed $message Dernière erreur sql
    * @param boolean @error Savoir si le message à envoyer est un message d'erreur ou de succès (true si erreur, false si succès)
    *
    * @return void
    */
   function logs($message) {
      $logger = new Logger('transactions');
      $logstream = new StreamHandler('../tools/error.log');
      $logger->pushHandler($logstream);

      $logger->info($message);
   }
}