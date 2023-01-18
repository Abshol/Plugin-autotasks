<?php
/**
 * Install hook
 *
 * @return boolean
 */
function plugin_autotasks_install() {
   global $DB;

   //instanciate migration with version
   $migration = new Migration(100);

   CronTask::register(AutoTasks::class, 'AutoTasks', MINUTE_TIMESTAMP, array('comment' => "Cette tâche permet d'automatiser le passage des tâches d'un ticket de 'en attente' à 'à faire' lorsque la précédente est réalisée", 'mode' => CronTask::MODE_EXTERNAL));
   
   //execute the whole migration
   $migration->executeMigration();
   
   return true;
}
/**
* Uninstall hook
*
* @return boolean
*/
function plugin_autotasks_uninstall() {
    // CronTask::unregister('AutoTasks');
    return true;
}