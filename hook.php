<?php
/**
 * Install hook
 *
 * @return boolean
 **/
include_once("../inc/includes.php");
function plugin_autotasks_install() {
    global $DB;
    //instanciate migration with version
    $migration = new Migration(100);
    CronTask::register(PluginAutotasksConfig::class, 'Autotasks-Config', 300, [
        'mode' => CronTask::MODE_EXTERNAL,
        'comment' => __('Autotasks - Permet de lancer le plug-in', 'autotasks')
    ]);
    if (!$DB->TableExists("glpi_plugin_autotaskslogs")) {
        $query = "CREATE TABLE `glpi`.`glpi_plugin_autotaskslogs` (`id` INT NOT NULL AUTO_INCREMENT , `user` INT NOT NULL , `hardreset` BOOLEAN NOT NULL, `date` DATE NOT NULL, `success` BOOLEAN NOT NULL, PRIMARY KEY (`id`));";
        $DB->query($query) or die("Erreur creation table glpi_plugin_autotaskslogs". $DB->error);
        $query = "ALTER TABLE `glpi_plugin_autotaskslogs` ADD FOREIGN KEY (user) REFERENCES glpi_users(id);";
        if (!$DB->query($query)) {
            $query = "DROP TABLE glpi_plugin_autotaskslogs";
            $DB->query($query);
            die("Erreur creation table glpi_plugin_autotaskslogs" . $DB->error);
        }
    }
    //execute the whole migration
    $migration->executeMigration();
    return true;
}
/**
 * Uninstall hook
 *
 * @return boolean
 **/
function plugin_autotasks_uninstall() {
    global $DB;
    $query = "DROP TABLE glpi_plugin_autotaskslogs";
    $DB->query($query);
    CronTask::unregister('AutoTasks');
    return true;
}