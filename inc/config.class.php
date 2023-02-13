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
      foreach ($result = $DB->request($sql) as $row) {
         if ($result->count() == 1) {
            $mess = $this->task($row, $DB);
         } else if ($result->count() > 1){
            switch ($mess = $this->task($row, $DB)) {
               case true:
                  $break = false;
                  break;
               case false:
                  $break = true;
                  break;
               if ($break) {
                  $this->logs("Une erreur est survenue lors du rechargement de la base: ".$DB->error);
                  break;
               }
            }
         } else {
            $this->logs("Aucune action possible dans la base de données");
            $mess = true;
         }
      }
      if (!$mess) {
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
      foreach ($resultset = $DB->request($sql) as $rows) {
         $before = false; //Cette variable sert à déterminer si le state de la tâche précédente est à 2 (true) ou non (false)
         $break = false;
         if ($rows['state'] != "2") {
            if ($before = true) { //Si la précédente tâche est passée à 2, on passe celle-ci à 1
               if ($this->groupVerif($rows, $DB)) {
                  if ($DB->update('glpi_tickettasks', ['state'=>1], ['id'=>$rows['id']])) {
                     $success = $this->groupChange($rows, $DB);
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
      // $sql = "SELECT COUNT(*) as `nombre` FROM glpi_groups_tickets WHERE tickets_id = " . $row['tickets_id'];
      // $resultset = $DB->query($sql);
      $resultset = $DB->request([
         'FROM' => 'glpi_groups_tickets',
         'WHERE' => ['tickets_id'=>$row['tickets_id']]
      ]);
      var_dump($resultset->count());
      if ($resultset->count() > 1) {
         return false;
      } else if ($resultset->count() == 1) {
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
   function groupDelete($row, $DB) {
      $req = $DB->request([
         'SELECT' => 'groups_id_tech',
         'FROM' => 'glpi_tickettasks',
         'WHERE' => [
            'tickets_id'=>$row['tickets_id'],
            'state'=>2]
         ]);
      foreach ($req as $roz) {
         $request = $DB->request([
            'FROM' => 'glpi_groups_tickets',
            'WHERE' => [
               'tickets_id'=>$row['tickets_id'],
               'groups_id'=>$roz['groups_id_tech'],
               'type'=>'2'
            ]
            ]);
            if ($request->count()) {
               $sql = "DELETE FROM glpi_groups_tickets WHERE groups_id = ".$roz['groups_id_tech']." AND tickets_id = ".$row['tickets_id'];
               if ($DB->query($sql)) {
                  $this->logs("Suppression de l'attribution du groupe ".$roz['groups_id_tech']." au ticket ".$row['tickets_id']." réussie");
                  return true;
               } else {
                  $this->logs("Echec de la suppression de l'attribution du groupe ".$roz['groups_id_tech']." au ticket ".$row['tickets_id']);
                  return false;
               }
            }

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
      if (
         $DB->update('glpi_groups_tickets', [
            'groups_id'=>$row['groups_id_tech']
         ], [
            'tickets_id'=>$row['tickets_id']
         ]
      )) {
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
      if ($DB->insert(
         'glpi_groups_tickets', [
            'tickets_id' => $row['tickets_id'],
            'groups_id' => $row['groups_id_tech'],
            'type' => 2
         ]
      )) {
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
      if ($result = $result = $DB->request([
         'FROM' => 'glpi_plugin_autotaskslogs',
         'WHERE' => [
            'user' => $userid,
            'date' => new QueryExpression('DATE(NOW())'),
            'hardreset' => 1
         ]
      ])) {
         if ($result->count() > $this->getNumbHardR($DB)) {
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
      // $sql = "SELECT number FROM glpi_plugin_autotasksconf WHERE name = 'maxHardR'";
      if ($result = $DB->request([
         'SELECT' => 'number',
         'FROM' => 'glpi_plugin_autotasksconf',
         'WHERE' => ['name' => 'maxHardR']
      ])) {
         $row = $result->next();
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
      if ($result = $DB->request([
         'FROM' => 'glpi_plugin_autotaskslogs',
         'WHERE' => [
            'user' => $userid,
            'date' => new QueryExpression('DATE(NOW())'),
            'hardreset' => 1
         ]
      ])) {
         $row = $result->count();
         return $row;
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
      $DB->insert(
         'glpi_plugin_autotaskslogs', [
            'user' => Session::getLoginUserID(),
            'hardreset' => ($hardreset?1:0),
            'date' => new QueryExpression('DATE(NOW())'),
            'success' => ($success?1:0)
         ]
      );
      if ($success) {
         return true;
      }
      return false;
   }

   /**
    * Supprime tous les logs dattant de 6 mois ou plus
    *
    * @param mysqli $DB Base de données
    *
    * @return boolean
    */
   function delTaskLogs($DB) {
      if ($DB->delete(
            'glpi_plugin_autotaskslogs', [
               'date' => ['<=', new QueryExpression('DATE_ADD(DATE(NOW()), INTERVAL -6 MONTH)')]
            ])) {
         $sql = "DELETE FROM glpi_plugin_autotaskslogs_changeconf WHERE date <= DATE_ADD(DATE(NOW()),INTERVAL -6 MONTH);";
         return $DB->delete(
            'glpi_plugin_autotaskslogs_changeconf', [
               'date' => ['<=', new QueryExpression('DATE_ADD(DATE(NOW()), INTERVAL -6 MONTH)')]
            ]);
      }
      return false;
   }

   /**
    * Insère dans les logs les changements dans la configuration du plug-in
    *
    * @param mysqli $DB Base de données
    * @param int $user Id de l'utilisateur connecté
    * @param string $config Configuration modifiée
    * @param string $description Description de l'action
    *
    * @return bool Si l'action s'est bien passée ou non
    */
   function confLog($DB, $user, $config, $description) {
      $insert = $DB->buildInsert(
         'glpi_plugin_autotaskslogs_changeconf', 
         ['user' => new Queryparam(), 'config' => new Queryparam(), 'date' => new Queryparam(), 'description' => new Queryparam()]
      );
      $stmt=$DB->prepare($insert);
      $date = date('Y-m-d H:i:s');
      $stmt->bind_param('isss', $user, $config, $date, $description);
      if ($stmt->execute()) {
         return true;
      }
      return false;
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
      if ($result = $DB->request([
         'FROM' => 'glpi_plugin_autotasksconf',
         'WHERE' => ['name' => $recherche]
      ])) {
         $row = $result->next();
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
    * @param bool $activate Si la configuration doit être activée (true) ou non (false)
    *
    * @return bool Si l'action s'est bien déroulée ou non
    */
   function activateConf($name, $activate) {
      global $DB;
      if ($DB->update('glpi_plugin_autotasksconf', [
            'activated' => ($activate?1:0)
         ], [
            'name' => $name
         ])) {
         return true;
      }
      return false;
   }
}  