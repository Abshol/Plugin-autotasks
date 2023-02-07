<?php $mess = "";
if (isset($_POST['submit'])) {
    if ($_POST['motdepasse'] === $_POST['confirmmdp']){
        try{
            $db = new PDO('mysql:host=localhost;dbname=glpi;charset=utf8', 'root', '');
            $sql = "SELECT COUNT(*) from glpi_users WHERE name = :nom";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":nom", $_POST['name'], PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();
            if ($user[0] > 0){
                $mess = "<span style='color:red';> Le nom d'utilisateur rentré est déjà utilisé</span>";
            }       
            $sql = "INSERT INTO glpi_users (name, password) VALUES (:nom, :pass)";
            $hash = password_hash($_POST['motdepasse'], PASSWORD_DEFAULT);
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":nom", $_POST['name'], PDO::PARAM_STR);
            $stmt->bindParam(":pass", $hash, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $mess = "<span style='color:green;'> Le compte a bien été crée</span>";
            }  
        }
        catch (Exception $e){
            die ($e->getMessage());
        }  
    }
    else {
        $mess = "<span style='color:red';>Les mots de passe rentrés ne sont pas identiques";
    }
}?>
<style>
    input{margin:1em;}
</style>
<form action="" method="post">
    <input type="text" name="name" placeholder="Nom de l'utilisateur" id=""><br>
    <input type="password" name="motdepasse" id="" placeholder="Entrez le mot de passe"><br>
    <input type="password" name="confirmmdp" id="" placeholder="Confirmez le mot de passe"><br>
    <input type="submit" value="Envoyer" name="submit">
    <?php echo $mess;?>
</form>