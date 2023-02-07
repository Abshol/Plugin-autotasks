<?php

class database {
    public function getConf() {
        global $DB;
        $sql = "SELECT * FROM glpi_plugin_autotasksconf WHERE name = 'form'";
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
}
?>