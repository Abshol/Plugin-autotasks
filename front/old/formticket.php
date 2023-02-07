<?php
$db = new PDO('mysql:host=localhost;dbname=glpi;charset=utf8', 'root', 'root');
if (isset($_POST['submit'])) {
    try{

        $sql = "INSERT INTO glpi_tickets(name, date, content, urgency, type) VALUES (:name, NOW(), :content, :urgency, :type)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":name", $_POST['titre'], PDO::PARAM_STR);
        $stmt->bindParam(":content", $_POST['desc'], PDO::PARAM_STR);
        $stmt->bindParam(":urgency", $_POST['urgency'], PDO::PARAM_INT);
        $stmt->bindParam(":type", $_POST['type'], PDO::PARAM_INT);
        $stmt->execute();
        
        $sql = "SELECT id FROM glpi_tickets WHERE name = :name AND content = :content AND urgency = :urgency AND type = :type";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":name", $_POST['titre'], PDO::PARAM_STR);
        $stmt->bindParam(":content", $_POST['desc'], PDO::PARAM_STR);
        $stmt->bindParam(":urgency", $_POST['urgency'], PDO::PARAM_INT);
        $stmt->bindParam(":type", $_POST['type'], PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetchAll();
        $idTicket = $res[0]['id'];
        $sql = "INSERT INTO glpi_tickets_users (tickets_id, users_id) VALUES (".$idTicket.", :user)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user', $_POST['demandeur'], PDO::PARAM_INT);
        $stmt->execute();
        
        for ($i = 1; $i <= 4; $i++) {
            $sql = "SELECT * FROM glpi_tasktemplates WHERE id = $i";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "INSERT INTO glpi_tickettasks (tickets_id, users_id, content, groups_id_tech, date_mod, date, date_creation, tasktemplates_id) VALUES (:idTicket, :userId, '".$res['content']."', ".intval($res['groups_id_tech']).", NOW(), NOW(), NOW(), ".intval($res['id']).")";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":idTicket", $idTicket, PDO::PARAM_INT);
            $stmt->bindParam(":userId", $_POST['demandeur'], PDO::PARAM_INT);
            $stmt->execute();
        }
        
    }
    catch (Exception $e){
        echo "\nPDO::errorInfo():\n";
        print_r($db->errorInfo());
    }  
}
?>
<style>
    input{margin:1em;}
</style>
<form action="" method="post">
    <label for="demandeur">Demandeur:</label>
    <select name="demandeur">
        <?php
            try {
                $sql= "SELECT id, name FROM glpi_users ORDER BY id";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($res as $value){
                    echo "<option value='".$value['id']."'>".$value['name']."</option>";
                }
            }
            catch (Exception $e){
                echo "\nPDO::errorInfo():\n";
                print_r($db->errorInfo());
            }
        ?>
    </select><br>
    <label for="type">Type:</label>
    <select name="type">
        <option value="1" selected>Incident</option>
        <option value="2">Requete</option>
    </select><br>
    <label for="urgency">Niveau d'urgence:</label>
    <select name="urgency">
        <option value="5">Très Haute</option>
        <option value="4">Haute</option>
        <option value="3" selected >Medium</option>
        <option value="2">Faible</option>
        <option value="1">Très Faible</option>
    </select><br>
    <label for="titre">Titre du ticket:</label>
    <input type="text" name="titre" id="" placeholder="Entrez votre titre"><br>
    <label for="desc">Description:</label>
    <textarea name="desc" id="" cols="30" rows="10" placeholder="Description du ticket"></textarea><br>
    <input type="submit" value="Envoyer" name="submit">
</form>