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

(new PluginautotasksConfig)
$web = '';
$form = '';
$formButton = '';
$webButton = '';

if (getConf('webphp')) {
   $web = "<input type='submit' class='inputAuto' name='webDeactivate' value='Désactiver la page web.php de débug'>";
   $webButton = "<a href='../web.php'><button class='autobutton'>Web.php</button></a>";
} else {
   $web = "<input type='submit' class='inputAuto' name='webActivate' value='Activer la page web.php de débug'>";
}

if (getConf('form')) { 
   $form = "<input type='submit' class='inputAuto' name='formDeactivate' value='Désactiver le formulaire de débug'>";
   $formButton = "<a href='Form/'><button class='autobutton'>Aller au formulaire</button></a>";
} else {
   $form = "<input type='submit' class='inputAuto' name='formActivate' value='Activer le formulaire de débug'>";
}
if (isset($_GET['envoyer'])) {
   if (isset($_GET['numbHardR'])) {
      if (!(new PluginautotasksConfig)->hardreset(Session::getLoginUserID(), $DB)) {
         if ((new PluginautotasksConfig)->confLog($DB, Session::getLoginUserID(), 'maxHardR', 'Changement de la limite de '.(new PluginautotasksConfig)->getNumbHardR($DB).' à '.$_GET['numbHardR'])) {
            $nombre = intval($_GET['numbHardR']);
            $nombre *= $nombre;
            $nombre = sqrt($nombre);
            if ((new PluginautotasksConfig)->changeHardR($DB, $nombre)) {
               header('Location: ./config.form.php');
            } else {
               header('Location: ./config.form.php?mess=HRChangeErr');
            }
         } else {
            header('Location: ./config.form.php?mess=HRChangeErr');
         }
      } else {
         header('Location: ./config.form.php?mess=HRChangeLimit');
      }
   }
}
if (isset($_GET['webActivate'])) {
   if ((new PluginautotasksConfig)->confLog($DB, Session::getLoginUserID(), 'webphp', 'Activation de web.php')) {
      if (!(new PluginautotasksConfig)->activateConf('webphp')) {
         header('Location: ./config.form.php?mess=ErrActDeact');
      }
   } else {
      header('Location: ./config.form.php?mess=ErrActDeact');
   }
   header('Location: ./config.form.php');
}
if (isset($_GET['formActivate'])) {
   if ((new PluginautotasksConfig)->confLog($DB, Session::getLoginUserID(), 'form', 'Activation du formulaire')) {
      if (!(new PluginautotasksConfig)->activateConf('form')) {
         header('Location: ./config.form.php?mess=ErrActDeact');
      }
   } else {
      header('Location: ./config.form.php?mess=ErrActDeact');
   }
   header('Location: ./config.form.php');
}
if (isset($_GET['webDeactivate'])) {
   if ((new PluginautotasksConfig)->confLog($DB, Session::getLoginUserID(), 'webphp', 'Désactivation de web.php')) {
      if (!(new PluginautotasksConfig)->deactivateConf('webphp')) {
         header('Location: ./config.form.php?mess=ErrActDeact');
      }
   } else {
      header('Location: ./config.form.php?mess=ErrActDeact');
   }
   header('Location: ./config.form.php');
}
if (isset($_GET['formDeactivate'])) {
   if ((new PluginautotasksConfig)->confLog($DB, Session::getLoginUserID(), 'form', 'Désactivation du formulaire')) {
      if (!(new PluginautotasksConfig)->deactivateConf('form')) {
         header('Location: ./config.form.php?mess=ErrActDeact');
      }
   } else {
      header('Location: ./config.form.php?mess=ErrActDeact');
   }
   header('Location: ./config.form.php');
}
if (!isset($_GET['mess'])) {
   $_GET['mess'] = '';
}
switch($_GET['mess']) {
   case "ErrActDeact":
      $mess = "<br><br><span class='error'>Erreur : Une erreur est survenue lors du changement de configuration</span>"; 
      break;
   case "resetSucc":
      $mess = "<br><br><span class='message'>L'action a bien été effectuée</span>";
      break;
   case "resetErr":
      $mess = "<br><br><span class='error'>Erreur : Une erreur est survenue lors de l'action</span>";
      break;
   case "HRLimit":
      $mess = "<br><br><span class='error'>Erreur : Vous avez atteint la limite journalière de 'hard-reset'</span>";
      break;
   case "HRCase":
      $mess = "<br><br><span class='error'>Veuillez cocher la case</span>";
      break;
   case "HRChangeErr":
      $mess = "<br><br><span class='error'>Erreur : Une erreur est survenue lors du changement de quotas</span>";
      break;
   case "HRChangeLimit":
      $mess = "<br><br><span class='error'>Erreur : Impossible de changer de quotas si votre quotas actuel est déjà atteint</span>";
      break;
   default:
      $mess = "";
      break;
}
if (isset($_GET['reset'])) {
   $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE date_mod BETWEEN DATE(NOW()) - interval 1 day AND DATE(NOW()) + interval 1 day AND state = 2";
   $reussite = (new PluginautotasksConfig)->starttask($sql);
   if ((new PluginautotasksConfig)->tasklog($reussite, $DB)) {
            header('Location: ./config.form.php?mess=resetSucc');
   } else {
      header('Location: ./config.form.php?mess=resetErr');
   }
}

if (isset($_GET['hardreset'])) {
   if (isset($_GET['verif'])) {
      if ((new PluginautotasksConfig)->hardreset(Session::getLoginUserID(), $DB)) {
         header('Location: ./config.form.php?mess=HRLimit');
      } else {
         $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY id)) AS `row`, id, tickets_id, date_mod, state FROM glpi_tickettasks WHERE state = 2";
         $reussite = (new PluginautotasksConfig)->starttask($sql);
         if ((new PluginautotasksConfig)->tasklog($reussite, $DB, true)) {
                  header('Location: ./config.form.php?mess=resetSucc');
         } else {
            header('Location: ./config.form.php?mess=resetErr');
         }
      }
   } else {
      header('Location: ./config.form.php?mess=HRCase');
   }
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
               <div class='form-object config' id='focusInput'>
                  <label for='numbHardR'>Nombre de 'hard-reset' authorisés par comptes:</label>
                  <input type='number' name='numbHardR' min='0' id='numbHardR' placeholder='Defaut=1 - Actuel=".(new PluginautotasksConfig)->getNumbHardR($DB)."'>
                  <input type='submit' id='focus' class='inputAuto subb' name='envoyer' value='Enregistrer les modifications'>
               </div>
               <div class='dropdown-menu config'>
                  <div class='menu-btn config'>Débug</div>
                  <div class='drop-container config'>
                     <input type='submit' class='inputAuto config' name='reset' value='Recharger les dernières 24h'>
                     <div class='form-object'>
                        <input type='submit' class='inputAuto config' name='hardreset' value='Recharger TOUTE la base de données' style='width:110%'>
                        <span class='checkbox'><input type='checkbox' name='verif' id='verif' value='true'><label for='verif'>Confirmez votre action</label></span>
                        <span class='desc'>Vous L'avez fait <strong>".(new PluginautotasksConfig)->getNumbHardRUser(Session::getLoginUserID(), $DB)."</strong> fois aujourd'hui</span>
                     </div>
                  </div>
               </div>
            </div>
         </form><br>
               $webButton
               $formButton
      </div>
      $mess
</div></div><script src='Form/javascript/script.js'></script>", 'autotasks');
Html::footer();





function getConf($name) {
   if ((new PluginautotasksConfig)->getConf($name)) {
      return true;
   } 
   return false;
}