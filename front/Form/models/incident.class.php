<?php
class incidentclass {
    /**
     * Insertion du ticket dans la base de données
     * 
     * @param mixed $post $_POST
     * 
     * @return bool true si ça s'est bien passé, false sinon
     */
    public function incident($post, $DB) {
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
            $sql = "INSERT INTO glpi_groups_tickets (tickets_id, groups_id, `type`) VALUES (LAST_INSERT_ID(), 6, 2)";
            if ($DB->query($sql)) {
                return true;
            }
        }
        return false;
    }
}
?>