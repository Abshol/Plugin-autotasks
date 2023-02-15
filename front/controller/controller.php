<?php
class controller {
    /**
     * Insertion du ticket dans la base de données uniquement si la descrption est écrite et que la case de téléphone est cochée
     * 
     * @param mixed $post On renseigne $_POST
     * @param mysqli $DB Base de données
     * 
     * @return void
     */
    public function materielSub($post, $DB) {
        if(isset($post['desc']) && isset($post['tel'])) {
            if ($post['tel'] == 'Oui'){
                if ((new demandeclass)->materiel($post, $DB, parse_ini_file("config.ini"))) {
                    header('Location: ?action=demandeSucc');
                } else {
                    header('Location: ?action=demandeErr');
                }
            } else {
               header('Location: ?action=demandeErr&type=noRequest');
            }
        }
        else {
            header('Location: ?action=demandeErr&type=empty');
        }
    }

    /**
     * Même chose que materielSub mais pour les incidents
     * 
     * @param mixed $post On renseigne $_POST
     * @param mysqli $DB Base de données
     * 
     * @return void
     */
    public function incidentSub($post, $DB) {
        if (isset($post['desc'])) {
            if ((new incidentclass)->incident($post, $DB, parse_ini_file("config.ini"))) {
                header('Location: ?action=incidentSucc');
            } else {
                header('Location: ?action=incidentErr');
             }
        }
    }
    public function index() {
        (new index)->accueil();
    }
    public function materiel($mess = "") {
        (new demandeform)->materiel($mess);
    }
    public function demande() {
        (new demandeform)->index();   
    }
    
    public function incident($mess = "") {
        (new incidentform)->index($mess);
    }

    public function unauthorized() {
        (new index)->unauthorized();
    }


    public function getConf() {
        return (new database)->getConf();
    }
}
?>