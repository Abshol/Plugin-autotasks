<?php

require('vues/demande.form.php');
require('vues/incident.form.php');
require('vues/index.form.php');

require('models/demande.class.php');
require('models/incident.class.php');

class controller {
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
                if ((new demandeclass)->materiel($post, $DB)) {
                    $this->materiel("<div class='message'>Votre ticket a bien été envoyé et votre demande sera traitée sous peu</div>");
                } else {
                   $this->materiel("<div class='error'>Une erreur est survenue lors de la création du ticket</div>");
                }
            } else {
               $this->materiel("<div class='message'>Votre ticket n'a pas été envoyé car aucune demande n'a été faite</div>");
            }
        }
        else {
           $this->materiel("<div class='error'>Merci de bien remplir tout les champs</div>");
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
            if ((new incidentclass)->incident($post, parse_ini_file('config.ini'))) {
                $this->incident("<span class='message'>Votre ticket a bien été crée et votre demande sera traitée dès que possible</span>");
            } else {
                $this->incident("<span class='error'>Une erreur est survenue lors de la création du ticket</span>");
             }
        }
    }
}
?>