<?php
/**
 * Install hook
 *
 * @return boolean
 **/
function plugin_autotasks_install() {
    global $DB;
    //instanciate migration with version
    $migration = new Migration(100);
    CronTask::register(PluginAutotasksAuto::class, 'Config', 300, [
        'mode' => CronTask::MODE_EXTERNAL,
        'comment' => __('Autotasks - Permet de lancer le plug-in', 'autotasks')
    ]);
    if (!$DB->TableExists("glpi_plugin_autotaskslogs")) {
        $query = "CREATE TABLE `glpi_plugin_autotaskslogs` (`id` INT NOT NULL AUTO_INCREMENT , `user` INT NOT NULL , `hardreset` BOOLEAN NOT NULL, `date` DATE NOT NULL, `success` BOOLEAN NOT NULL, PRIMARY KEY (`id`));";
        $DB->query($query) or die("Erreur creation table glpi_plugin_autotaskslogs". $DB->error);
        $query = "ALTER TABLE `glpi_plugin_autotaskslogs` ADD FOREIGN KEY (user) REFERENCES glpi_users(id);";
        if (!$DB->query($query)) {
            $query = "DROP TABLE glpi_plugin_autotaskslogs";
            die("Erreur creation table glpi_plugin_autotaskslogs" . $DB->error);
        }

        $query = "CREATE TABLE `glpi_plugin_autotasksconf` (`id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(50) NOT NULL , `activated` BOOLEAN NOT NULL, `description` VARCHAR(300) NOT NULL, PRIMARY KEY (`id`));";
        if (!$DB->query($query)) {
            $query = "DROP TABLE glpi_plugin_autotasksconf";
            $DB->query($query);
            die("Erreur creation table glpi_plugin_autotasksconf". $DB->error);
        } 
            
        $query = "INSERT INTO `glpi_plugin_autotasksconf` (`name`, `activated`, `description`) VALUES ('webphp', FALSE, 'Fichier web de débug'), ('form', FALSE, 'Formulaire de création de tickets')";
        if (!$DB->query($query)) {
            $query = "DROP TABLE glpi_plugin_autotasksconf";
            $DB->query($query);
            die("Erreur lors de l'insertion des configurations  ". $DB->error);
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
    $query = "DROP TABLE glpi_plugin_autotasksconf";
    $DB->query($query);
    CronTask::unregister('Config');
    return true;
}