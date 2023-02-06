<?php
global $DB, $CFG_GLPI;
if(file_exists("../inc/includes.php")) {
   include("../inc/includes.php");
} else {
   include("../../../inc/includes.php");
}
include("../inc/config.class.php");

if (!defined('GLPI_ROOT')) {
   echo __("Vous n'avez pas l'authorisation d'accéder à cette page", 'autotasks');
} 
Session::checkRight("config", UPDATE);

Plugin::load('autotasks');

$autotsk = new PluginautotasksConfig();
$web = '';
$form = '';
$formButton = '';
$webButton = '';

if (getConf($autotsk, 'webphp')) {
   $web = "<input type='submit' class='inputAuto' name='webDeactivate' value='Désactiver la page web.php de débug'>";
   $webButton = "<a href='../web.php'><button class='autobutton'>Web.php</button></a>";
} else {
   $web = "<input type='submit' class='inputAuto' name='webActivate' value='Activer la page web.php de débug'>";
}

if (getConf($autotsk, 'form')) { 
   $form = "<input type='submit' class='inputAuto' name='formDeactivate' value='Désactiver le formulaire de débug'>";
   $formButton = "<a href='Form/'><button class='autobutton'>Aller au formulaire</button></a>";
} else {
   $form = "<input type='submit' class='inputAuto' name='formActivate' value='Activer le formulaire de débug'>";
}

if (isset($_GET['webActivate'])) {
   $autotsk->activateConf('webphp');
   header('Location: ./config.form.php');
}
if (isset($_GET['formActivate'])) {
   $autotsk->activateConf('form');
   header('Location: ./config.form.php');
}
if (isset($_GET['webDeactivate'])) {
   $autotsk->deactivateConf('webphp');
   header('Location: ./config.form.php');
}
if (isset($_GET['formDeactivate'])) {
   $autotsk->deactivateConf('form');
   header('Location: ./config.form.php');
}

Html::header("AutoTasks Config", $_SERVER['PHP_SELF'], "config", "plugins");
echo __("<link href='Form/css/style.css' rel='stylesheet'> 
<div class='autotasks'>
   <div class='container'>
      <div class='container-header config'>
         <span class='header-title'>Configuration du plug-in</span>
         <span class='header-desc' style='font-size:0.6em;'>Si vous n'êtes pas sur de ce que vous faites, merci de vous référer au README.txt dans les fichiers du plug-in</span>
      </div>
      <div class='container-form config'>
         <form method='GET' action=''>
            <div class='container-content'>
               <div class='form-object'>
                  $web
                  $form
               </div>
               <div class='dropdown-menu config'>
                  <div class='menu-btn config'>Débug</div>
                  <div class='drop-container config'>
                     <input type='submit' class='inputAuto config' name='reset' value='Recharger les dernières 24h'>
                     <div class='form-object'>
                        <input type='submit' class='inputAuto config' name='hardreset' value='Recharger TOUTE la base de données'>
                        <span class='checkbox'><input type='checkbox' name='verif' id='verif' value='true'><label for='verif'>Confirmez votre action</label></span>
                     </div>
                  </div>
               </div>
            </div>
         </form><br>
               $webButton
               $formButton
      </div>", "autotasks");

      
if (isset($_GET['reset'])) {
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
   $reussite = $autotsk->starttask($sql);
   if ($autotsk->tasklog($reussite, $DB)) {
      echo __("<br><br><span class='message'>L'action a été réalisée avec succès</span>", 'autotasks');
   } else {
      echo __("<br><br><span class='error'>Une erreur s'est produite lors du rechargement de la base</span>", 'autotasks');
   }
}

if (isset($_GET['hardreset'])&& isset($_GET['verif'])) {
   if ($_GET['verif'] == 'true') {
      if ($autotsk->hardreset(Session::getLoginUserID(), $DB)) {
         echo __("<br><br><span class='error'>Erreur: Vous avez déjà effectué cette action dans la journée</span>", 'autotasks');
      } else {
         $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE state = 2";
         $reussite = $autotsk->starttask($sql);
         if ($autotsk->tasklog($reussite, $DB, true)) {
            echo __("<br><br><span class='message'>L'action a été réalisée avec succès</span>", 'autotasks');
         } else {
            echo __("<br><br><span class='error'>Une erreur s'est produite lors du rechargement de la base</span>", 'autotasks');
         }
      }
   } else {
     echo __("<br><br><span class='error'>Merci de bien vouloir cocher la case</span>", 'autotasks');  
   }
}
echo __("</div></div><script src='Form/javascript/script.js'></script>", 'autotasks');
Html::footer();

function getConf($autotsk, $name) {
   if ($autotsk->getConf($name)) {
      return true;
   } 
   return false;
}