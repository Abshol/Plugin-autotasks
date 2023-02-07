<?php
include("phpheader.php");

if (!isset($_GET['action'])) {
    $_GET['action'] = '';
}
if (!(new controller)->getConf()) {
    (new controller)->unauthorized();
    die();
}
switch ($_GET['action']) {
    case "demande":
        (new controller)->demande();
        break;
    case "demandeSucc":
        (new controller)->materiel("<div class='message'>Votre ticket a bien été envoyé et votre demande sera traitée sous peu</div>");
        break;
    case "demandeErr":
        if (!isset($_GET['type'])) {
            $_GET['type'] = '';
        }
        switch ($_GET['type']) {
            case 'noRequest':
                (new controller)->materiel("<div class='message'>Votre ticket n'a pas été envoyé car aucune demande n'a été faite</div>");
                break;
            case 'empty':
                (new controller)->materiel("<div class='error'>Merci de bien remplir tout les champs</div>");
                break;
            default:
                (new controller)->materiel("<div class='error'>Une erreur est survenue lors de la création du ticket</div>");
                break;
        }
        break;
    case "incident":
        (new controller)->incident();
        break;
    case "incidentSucc":
        (new controller)->incident("<span class='message'>Votre ticket a bien été crée et votre demande sera traitée dès que possible</span>");
        break;    
    case "incidentErr":
        (new controller)->incident("<span class='error'>Une erreur est survenue lors de la création du ticket</span>");
        break;
    case "materiel":
        (new controller)->materiel();
        break;
    default:
        if (isset($_GET['materielSub'])) {
            (new controller)->materielSub($_GET, $DB);
            break;
        }
        else if (isset($_GET['incidentSub'])) {
            (new controller)->incidentSub($_GET, $DB);
            break;
        }
        (new controller)->index();
        break;
}
