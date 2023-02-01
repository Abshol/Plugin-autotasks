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
                    <div class='container-header'><a href='?action=' class='goback'><i class='gg-mail-reply'></i></a>Incident</div>
                    <div class='container-content'>
                    <form action='?action=incidentSub' method='POST'>
                    <div class='container-form'>
                        <div class='description'>
                            <textarea class='editor'id='editor'name='desc' cols='15' rows='5' placeholder='Décrivez votre problème' required></textarea>
                        </div>
                        <input type='submit' name='materielSub' value='Envoyer'>
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