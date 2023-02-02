<?php
class index 
{
   /**
    * Index permettant de choisir si l'on souhaite faire une demande ou déclarer un incident
    * 
    * @return void
    */
   public function accueil() {
      include('header.php');
      echo "
      <div class='container'>
      <div class='container-header'><span class='header-title'>Quel type de ticket voulez-vous créer ?</span></div>
      <div class='container-content'>
         <a href='?action=demande'><button class='autobutton'>Demande</button></a> <a href='?action=incident'><button class='autobutton'>Incident</button></a>
      </div>";
      include('footer.php');
   } 
}
?>