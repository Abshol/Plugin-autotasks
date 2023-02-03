<?php
class incidentform
{
    /**
     * Affichage du formulaire d'incident
     * 
     * @param mixed $mess Message à afficher en cas de réussite ou d'echec
     * 
     * @return void
     */
    public function index($mess = "")
    {
        include('header.php');
        ?>      <div class='container'>
                    <div class='container-header'><a href='?action=' ><box-icon name='left-arrow-alt' color='#ffffff' ></box-icon></a><span class='header-title'>Incident</span></div>
                    <div class='container-content'>
                    <form action='?action=incidentSub' method='GET'>
                    <div class='container-form'>
                        <div class='description'>
                            <textarea class='editor'id='editor'name='desc' cols='15' rows='5' placeholder='Décrivez votre problème' required></textarea>
                        </div>
                        <input class='inputAuto' type='submit' name='incidentSub' value='Envoyer'>
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