<?php
define('autotasks_VERSION', '2.2.2');
global $CFG_GLPI;
/**
 * Init the hooks of the plugins - Needed
 *
 * @return void
 */
function plugin_init_autotasks() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['config_page']['autotasks'] = 'front/config.form.php';
   $PLUGIN_HOOKS['add_javascript']['autotasks'] = 'front/javascript/script.js';
   $PLUGIN_HOOKS['add_css']['autotasks'] = 'front/css/style.css';
   $PLUGIN_HOOKS['csrf_compliant']['autotasks'] = true;
   Plugin::registerClass('autotasks');

}

/**
 * Get the name and the version of the plugin
 *
 * @return array
 */
function plugin_version_autotasks() {
   return [
      'name'           => 'autotasks',
      'version'        => autotasks_VERSION,
      'author'         => 'Abshol',
      'license'        => 'MIT License',
      'homepage'       => 'https://github.com/Abshol',
      'requirements'   => [
         'glpi'   => [
            'min' => '9.5.0',
            'max' => '10.0.0'
         ]
      ]
   ];
}

/**
 * Vérifie la configuration de GLPI et retourne true si elle est bonne
 * Ne peut écrire un message que si $verbose = true
 *
 * @param boolean $verbose Active le mode verbeux, false par défaut
 *
 * @return boolean
 */
function plugin_autotasks_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo "Installed, but not configured";
   }
   return false;
}