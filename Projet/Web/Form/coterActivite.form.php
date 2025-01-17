<?php
/**
 * Created by PhpStorm.
 * User: JulienTour
 * Date: 24/11/2015
 * Time: 23:42
 */

$uam = new User_ActivityManager(connexionDb());
$act = $uam->getActIdByUserId($_SESSION['User']);
$am = new ActivityManager(connexionDb());
$activity = $am->getActivityById($act[0]['id_activity']);
?>


<form class="form-horizontal" action="coterActivite.page.php" method="post">
    <h1 align="center"> Donnez une note à l'activité que vous être en train d'effectuer !</h1>
    <?php echo "<h1 align='center'> Votre activité est ".$activity->getLibelle()." </h1>"; ?>
    <h2 align="center"> La cotation va de 1 à 5 de gauche à droite (la troisième étoile vaut 3/5 par exemple)</h2>
    <br><br>
    <span class="radioCote">
        <label class="radio-inline"><input type="radio" name="cote" value="1"><img height='40px' width='40px' src="../Images/ratings/star.ico" alt="1/5"/></label>
        <label class="radio-inline"><input type="radio" name="cote" value="2"><img height='40px' width='40px' src="../Images/ratings/star.ico" alt="2/5"/></label>
        <label class="radio-inline"><input type="radio" name="cote" value="3"><img height='40px' width='40px' src="../Images/ratings/star.ico" alt="3/5"/></label>
        <label class="radio-inline"><input type="radio" name="cote" value="4"><img height='40px' width='40px' src="../Images/ratings/star.ico" alt="4/5"/></label>
        <label class="radio-inline"><input type="radio" name="cote" value="5"><img height='40px' width='40px' src="../Images/ratings/star.ico" alt="5/5"/></label>
    </span>
    <br><br><br>
    <button class='btn btn-success col-sm-4' type='submit' id='Accepter' name='Accepter'>Je la note !</button>
    <button class="btn btn-warning col-sm-4" type='submit' id='Report' name='Report'>Plus tard ! (Vous devrez attendre 6h)</button>
    <button class="btn btn-danger col-sm-4" type='submit' id='Refuser' name='Refuser'>Je ne la note pas et je veux changer d'activité !</button>
</form>