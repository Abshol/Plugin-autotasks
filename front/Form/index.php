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
