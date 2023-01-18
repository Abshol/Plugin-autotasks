<?php
// namespace GlpiPlugin\autotasks;
include ("../../../inc/includes.php");

class PluginautotasksAutoTasks extends CommonDBTM
{
   /**
    * Give cron information
    *
    * @param $name : automatic action's name
    *
    * @return arrray of information
    **/
   static function cronInfo($name)
   {
      switch ($name) {
         case 'autotasks':
            return array(
               'description' => __('Finds in the database all tasks that are set to 0 when the one before is set to 2, and sets it to 1')            );
      }
      return [];
   }
   /**
    * Cron action on notification queue: send notifications in queue
    *
    * @param CommonDBTM $task for log (default NULL)
    *
    * @return integer either 0 or 1
    **/
   static function cronAutoTasks($task = NULL)
   {
      global $DB, $CFG_GLPI;
      $cron_status = 0;
      $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
      if ($result = $DB->query($sql, $DB->error())) {

         if ($DB->numrows($result) > 0) { //Vérification si le Select a bien récupéré des données, si non, la procédure ne se déclenche pas

            while ($row = $DB->fetch_assoc($result)) {
               $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, state, tickets_id, content FROM glpi_tickettasks WHERE tickets_id = " . $row['tickets_id'];

               if ($resultset = $DB->query($sql, $DB->error())) {
                  if ($resultset = $DB->query($sql, $DB->error())) {

                     $before = false; //Cette variable sert à déterminer si le state de la tâche précédente est à 2 (true) ou non (false)
                     $i = 0;

                     foreach ($rows = $DB->fetch_assoc($resultset) as $key => $value) {
                        if ($value['state'] != 2) {
                           if ($before = true) { //Si la précédente tâche est passée à 2, on passe celle-ci à 1
                              $sql = "UPDATE glpi_tickettasks SET state = 1 WHERE id = " . $value['id'] . ";";
                              if ($result = $DB->query($sql, $DB->error())) {
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
         }
         return $cron_status;
      }
   }
}