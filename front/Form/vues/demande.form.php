<?php
class demandeform 
{
     /**
      * Index où l'on choisi le formulaire de demande à afficher
      * 
      * @return void
      */
     public function index(){
        include('header.php');
        echo "
        <div class='container'>
            <div class='container-header'><a href='?action=' class='goback'><i class='gg-mail-reply'></i></a>Type de Demande</div>
            <div class='container-content'><a href='?action=materiel'><button class='autobutton'>Demande de matériel</button></a></div>
        </div>";
        include('footer.php');
    }

    /**
     * Affichage du formulaire de demande de matériel
     * 
     * @param mixed $mess Message à afficher en cas d'erreur ou de réussite
     * 
     * @return void
     */
    public function materiel($mess = ''){
        include('header.php');
?>      <div class='container'>
            <div class='container-header'><a href='?action=demande' class='goback'><i class="gg-mail-reply"></i></a>Demande de matériel</div>
            <div class='container-content'>
            <form action='?action=materielSub' method='get'>
            <div class='container-form'>
                <div class='description'>
                    <textarea class='editor'id='editor'name='desc' cols='15' rows='5' placeholder='Description de votre demande' required></textarea>
                </div>
                <div class='dropdown-menu'>
                    <div class='menu-btn'>Téléphone</div>
                    <div class='drop-container'>
                    <span>Avez-vous besoin d'un téléphone ?</span>
                        <span>Oui</span> <input type='radio' name='tel' value='Oui'>
                        <span>Non</span> <input type='radio' name='tel' value='Non' checked>
                    </div>
                </div>
                <input class='inputAuto' type='submit' name='materielSub' value='Envoyer'>
                <?php echo $mess;?>
            </div>
            </form>
            </div>
        </div>
<?php
        include('footer.php');
    }
}
?>