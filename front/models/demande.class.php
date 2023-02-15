<?php
class demandeclass {
    /**
     * Insertion du ticket dans la base de données
     * 
     * @param mixed $post $_POST
     * @param mysqli $DB Base de données
     * 
     * @return bool true si ça s'est bien passé, false sinon
     */
    public function materiel($post, $DB, $ini) {
        $insert = $DB->buildInsert(
            'glpi_tickets',
            [
                'name' => new Queryparam(),
                'date' => new Queryparam(),
                'content' => new Queryparam()
            ]
        );
        $stmt = $DB->prepare($insert);
        $nom = "Demande de matériel";
        $date = date('Y-m-d H:i:s');
        $stmt->bind_param('sss', $nom, $date, $post['desc']);
        if ($stmt->execute()) {
            $id = $DB->insertId();
            if ($post['tel'] === 'Oui') {
                $sql = "INSERT INTO glpi_tickettasks (`tickets_id`, `groups_id_tech`, `date`, `content`) VALUES ($id, ".$ini['group_demande_tel'].", NOW(), 'Acheter Téléphone')";
                if ($DB->query($sql)) {
                    $sql = "INSERT INTO `glpi_tickets_users` (`tickets_id`, `users_id`, `type`) VALUES ($id, ".Session::getLoginUserID().", 1)";
                    if ($DB->query($sql)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
?>