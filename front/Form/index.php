<?php
global $DB, $CFG_GLPI;

include("../../../../inc/includes.php");

if (!defined('GLPI_ROOT')) {
   echo "Vous n'avez pas l'authorisation d'accéder à cette page";
} 
Session::checkRight("config", UPDATE); 
$style = '';
if (isset($_GET['hardreload']) && !isset($_GET['verif'])) {
   $style = "color:red;";
}

Plugin::load('autotasks');

require('controller/controller.php');
if (!isset($_GET['action'])) {
    $_GET['action'] = '';
}

switch ($_GET['action']) {
    case "demande":
        (new controller)->demande();
        break;
    case "incident":
        (new controller)->incident();
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
