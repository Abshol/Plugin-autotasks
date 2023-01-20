<?php
define('autotasks_VERSION', '1.0');
// Récupération du fichier includes de GLPI, permet l'accès au cœur
// include ("../inc/includes.php");
global $CFG_GLPI;

/**
 * Init the hooks of the plugins - Needed
 *
 * @return void
 */
function plugin_init_autotasks() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['config_page']['autotasks'] = 'front/config.form.php';
   Plugin::registerClass('autotasks');

   $PLUGIN_HOOKS['csrf_compliant']['autotasks'] = true;
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
      'license'        => '',
      'homepage'       => 'https://github.com/Abshol',
      'requirements'   => [
         'glpi'   => [
            'min' => '9.5.0'
         ]
      ]
   ];
}

/**
 * Check configuration process for plugin : need to return true if succeeded
 * Can display a message only if failure and $verbose is true
 *
 * @param boolean $verbose Enable verbosity. Default to false
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

/**
 * Optional: defines plugin options.
 *
 * @return array
 */
function plugin_autotasks_options() {
   return [
      Plugin::OPTION_AUTOINSTALL_DISABLED => true,
   ];
}
