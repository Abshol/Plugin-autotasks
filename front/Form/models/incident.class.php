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
        $sql = "INSERT INTO glpi_tickets (`name`, `date`, `content`) VALUES ('Incident', NOW(), ?)";
        $stmt = $DB->prepare($sql);
        $stmt->bind_param('s', $post['desc']);
        if ($stmt->execute()) {
            $ticketId = $DB->insert_id;
            $sql = "INSERT INTO glpi_groups_tickets (tickets_id, groups_id, `type`) VALUES ($ticketId, 6, 2)";
            if ($DB->query($sql)) {
                return true;
            }
        }
        return false;
    }
}
?>