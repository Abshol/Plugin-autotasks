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
            $this->logs("Aucune action possible dans la base de données");
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
                        $success = $this->groupChange($rows, $DB);
                     } else {
                        $success = false;
                     }
                  } else {
                     $this->groupDelete($rows, $DB, $lastGroupId);
                  }
                  $break = true;
               }
               $before = false;
            } else {
               $lastGroupId = $rows['groups_id_tech'];
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
    * Fonction vérifiant si plusieurs groupes sont affiliés au ticket, renvoie false si c'est le cas, true si non
    * Dans le cas où le groupe n'existe pas ($row['nombre'] == 0), on appelle groupCreate pour créer une affiliation à un groupe
    * 
    * @param mixed $row Lignes récupérées via la requête précédente 
    * @param mysqli $DB Base de données
    *
    * @return boolean
    */
   function groupVerif($row, $DB) {
      $sql = "SELECT COUNT(*) as `nombre` FROM glpi_groups_tickets WHERE tickets_id = " . $row['tickets_id'];
      $resultset = $DB->query($sql);
      $rows = $DB->fetch_assoc($resultset);
      if ($rows['nombre'] > 1) {
         return false;
      } else if ($rows['nombre'] == 1) {
         return true;
      } else {
         return $this->groupCreate($row, $DB);
      }
   }

   /**
    * Dans le cas où il y a plusieurs groupes, cette fonction va supprimer celui dont la tâche est terminée
    *
    * @param mixed $row Lignes récupérées via la requête précédente 
    * @param mysqli $DB Base de données
    * @param int $lastGroupId Dernier id de groupe récupéré (celui que nous allons supprimer de la table) 
    *
    * @return boolean
    */
   function groupDelete($row, $DB, $lastGroupId) {
      $sql = "DELETE FROM glpi_groups_tickets WHERE groups_id = $lastGroupId AND tickets_id = ".$row['tickets_id'];
      if ($DB->query($sql)) {
         $this->logs("Suppression de l'attribution du groupe $lastGroupId au ticket ".$row['tickets_id']." réussie");
         return true;
      } else {
         $this->logs("Echec de la suppression de l'attribution du groupe $lastGroupId au ticket ".$row['tickets_id']);
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
   function groupChange($row, $DB) {
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
    * Fonction permettant de créer l'attribution du groupe de la tâche suivante au ticket dans le cas où aucun n'était attribué
    * 
    * @param mixed row Lignes récupérées via la requête précédente
    * @param mysqli $DB Base de données 
    *
    * @return boolean Selon si cela s'est bien déroulé
    */
    function groupCreate($row, $DB) {
      // Vérification
      // On change l'affiliation de ce ticket à ce groupe
      $sql = "INSERT INTO glpi_groups_tickets (tickets_id, groups_id, `type`) VALUES (" . $row['tickets_id'] . ", " . $row['groups_id_tech'] . ",2)";
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
    * Cette fonction permet de savoir si l'utilisateur connecté a dépassé la limite de "hardreset" ou non, si oui, renvoie false
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
         if ($row['user'] > $this->getNumbHardR($DB)) {
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
    * Récupère le nombre de 'hard-reset' possible en une journée
    *
    * @param mysqli $DB Base de données
    *
    * @return int Nombre de fois possible en une journée
    */
   function getNumbHardR($DB) {
      $sql = "SELECT number FROM glpi_plugin_autotasksconf WHERE name = 'maxHardR'";
      if ($result = $DB->query($sql)) {
         $row = $DB->fetch_assoc($result);
         return $row['number'];
      } else {
         die("Erreur lors de la recherche des configurations");
      }

   }

   /**
    * Modifie le nombre de 'hard-reset' possible par jours
    *
    * @param mysqli $DB Base de données
    * @param int $nombre Nombre envoyé par l'utilisateur
    *
    * @return bool Si l'action s'est bien déroulée ou non
    */
   function changeHardR($DB, $nombre) {
      $update = $DB->buildUpdate(
         'glpi_plugin_autotasksconf', 
         ['number' => new Queryparam()], 
         ['name' => 'maxHardR']
      );
      $stmt=$DB->prepare($update);
      $stmt->bind_param('i', $nombre);

      if ($stmt->execute()) {
         return true;
      }
      return false;
   }

   /**
    * Récupère le nombre de fois qu'un utilisateur a fait un 'hard-reset' dans la journée
    *
    * @param int $userid Id de l'utilisateur
    * @param mysqli $DB Base de données
    *
    * @return int Nombre de fois que l'utilisateur a effectué l'action
    */
   function getNumbHardRUser($userid, $DB) {
      $sql = "SELECT COUNT(*) AS user FROM glpi_plugin_autotaskslogs WHERE user = $userid AND `date` = DATE(NOW()) AND hardreset = 1;";
      if ($result = $DB->query($sql)) {
         $row = $DB->fetch_assoc($result);
         return $row['user'];
      }
   }
   /**
    * Permet de logs les erreurs survenues lors des requêtes
    * 
    * @param mixed $message Message à envoyer aux logs
    *
    * @return void
    */
   function logs($message) {
      $logger = new Logger('transactions');
      $logstream = new StreamHandler('../tools/history.log');
      $logger->pushHandler($logstream);

      $logger->info($message);
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
    * Supprime tous les logs dattant de 6 mois ou plus (En test)
    *
    * @param mysqli $DB Base de données
    *
    * @return boolean
    */
   function delTaskLogs($DB) {
      $sql = "DELETE FROM glpi_plugin_autotaskslogs WHERE date <= DATE_ADD(DATE(NOW()),INTERVAL -1 DAY);"; //Tout les 1 jours pour l'instant pour cause de test
      return $DB->query($sql);
   }

   /**
    * Recherches si la configuration passée en paramètre est activée (True) ou non (False)
    *
    * @param string $recherche Nom de l'option à rechercher
    *
    * @return boolean Vrai si l'option est activée, False si non
    */
   function getConf($recherche) {
      global $DB;
      $sql = "SELECT * FROM glpi_plugin_autotasksconf WHERE name = '$recherche'";
      if ($result = $DB->query($sql)) {
         $row = $DB->fetch_assoc($result);
         if ($row['activated'] == 0) {
            return false;
         } else {
            return true;
         }
      } else {
         die('Erreur lors de la recherche des configurations');
      }
   }

   /**
    * Active une configuration dont le nom est passé en paramètre
    *
    * @param string $name Nom de la configuration à activer
    *
    * @return bool Si l'action s'est bien déroulée ou non
    */
   function activateConf($name) {
      global $DB;
      $sql = "UPDATE `glpi_plugin_autotasksconf` SET `activated`= 1 WHERE name = '$name'";
      if ($DB->query($sql)) {
         return true;
      }
      return false;
   }

   /**
    * Désactive une configuration dont le nom est passé en paramètre
    *
    * @param string $name Nom de la configuration à désactiver
    *
    * @return bool Si l'action s'est bien déroulée ou non
    */
   function deactivateConf($name) {
      global $DB;
      $sql = "UPDATE `glpi_plugin_autotasksconf` SET `activated`= 0 WHERE name = '$name'";
      if ($DB->query($sql)) {
         return true;
      }
      return false;
   }
}  