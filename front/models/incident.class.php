<?php
class incidentclass {
    /**
     * Insertion du ticket dans la base de données
     * 
     * @param mixed $post $_POST
     * 
     * @return bool true si ça s'est bien passé, false sinon
     */
    public function incident($post, $DB, $ini) {
        $insert = $DB->buildInsert(
            'glpi_tickets',
            [
                'name' => new Queryparam(),
                'date' => new Queryparam(),
                'content' => new Queryparam()
            ]
        );
        $stmt = $DB->prepare($insert);
        $nom = "Incident";
        $date = date('Y-m-d H:i:s');
        $stmt->bind_param('sss', $nom, $date, $post['desc']);
        if ($stmt->execute()) {
            $id = $DB->insertId();
            $sql = "INSERT INTO glpi_groups_tickets (tickets_id, groups_id, `type`) VALUES ($id, ".$ini['group_incident'].", 2)";
            if ($DB->query($sql)) {
                $sql = "INSERT INTO `glpi_tickets_users` (`tickets_id`, `users_id`, `type`) VALUES ($id, ".Session::getLoginUserID().", 1)";
                if ($DB->query($sql)) {
                    return true;
                }
            }
        }
        return false;
    }
}
?>