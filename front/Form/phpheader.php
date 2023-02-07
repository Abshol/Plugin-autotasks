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

require('models/demande.class.php');
require('models/incident.class.php');
require('models/db.class.php');

require('vues/demande.form.php');
require('vues/incident.form.php');
require('vues/index.form.php');

?>