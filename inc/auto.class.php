<?php
include("config.class.php");

class PluginautotasksAuto extends CommonDBTM 
{
    /**
     * Give cron information
     *
     * @param $name : automatic action's name
     *
     * @return array of information
     */
    static function cronInfo($name) {
        switch ($name) {
           case 'Autotasks-CronTask':
              return array(
                 'description' => __('Finds in the database all tasks that are set to 0 when the one before is set to 2, and sets it to 1'));
        }
        return array();
     }
     
     /**
      * Cron action on notification queue: send notifications in queue
      *
      * @param CommonDBTM $task : for log (default NULL)
      *
      * @return integer either 0 or 1
      */
      public static function cronConfig($task = NULL) {
         global $DB;
         $autotsk = new PluginautotasksConfig();
         $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
         $success = $autotsk->starttask($sql);
         if ($success) {
            $autotsk->logs("La tâche a été effectuée avec succès");
         } else {
            $autotsk->logs("Erreur lors du lancement de la tâche automatique");
         }
         // $success = $autotsk->delTaskLogs($DB); TESTING
         return intval($success);
      }
}