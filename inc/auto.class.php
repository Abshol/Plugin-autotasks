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
           case 'Autotasks':
              return array(
                 'description' => __('Récupères toutes les tâches finies pour automatiser l\'escalade des tickets'));
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
      public static function cronAutoTasks($task = NULL) {
         global $DB;
         $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
         $success = (new PluginautotasksConfig)->starttask($sql);
         if ($success) {
            (new PluginautotasksConfig)->logs("La tâche a été effectuée avec succès");
         } else {
            (new PluginautotasksConfig)->logs("Erreur lors du lancement de la tâche automatique");
         }
         $success = (new PluginautotasksConfig)->delTaskLogs($DB);
         return intval($success);
      }
}