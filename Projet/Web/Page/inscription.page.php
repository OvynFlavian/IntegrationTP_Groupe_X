<?php
/**
 * Created by PhpStorm.
 * User: Flavian Ovyn
 * Date: 1/10/2015
 * Time: 14:35
 */
    require "../Library/constante.lib.php";
    require "../Library/Fonctions/Fonctions.php";
    initRequire();
    initRequireEntityManager();
    initRequirePage("inscription");

    $configIni = getConfigFile();
    startSession();
    connexionDb();
    $isConnect = isConnect();
    if($isConnect)header("Location:../");
    if(isPostFormulaire() && isValidBis()['Retour']) {
        addDB();
        $class = 'success';
        $tabRetour['Error'][] = "Votre inscription est effective, vous avez reçu un mail avec votre code d'activation !";

    } else if (isPostFormulaire() and !isValidBis()['Retour']) {
        foreach (isValidBis()['Error'] as $elem) {
            $class = 'danger';
            $tabRetour['Error'][] = $elem;
        }
    }
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="icon" type="image/png" href="../Images/favicon.png" />
    <link rel="stylesheet" type="text/css" href="../vendor/twitter/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../Style/general.css">

    <script src="https://code.jquery.com/jquery-2.1.4.min.js" defer></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" defer></script>
    <script src="dist/js/bootstrap-submenu.min.js" defer></script>

    <link rel="stylesheet" type="text/css" href="../personalisation.css">
</head>
<body>
<section class="container">
    <header>
        <?php include("../Menu/menuGeneral.lib.php");?>
    </header>
    <div class="col-md-2 clearfix" id="sub-menu-left">

    </div>
    <section class="col-lg-8 jumbotron">
        <h1> <img class="jumbotitre" src="../Images/bannieres/inscription.png" alt="logo" id='image-media'></h1>
        <p class="jumbotexte">Rentrez vos données personnelles afin de recevoir un code d'activation par mail </p>
    </section>
    <section class="row">
        <article class="col-sm-12">
            <?php
            include("../Form/inscription.form.php");
            ?>
        </article>
    </section>
    <?php if(isset($tabRetour['Error'])){?>
        <section class="alert-dismissible">
            <?php
            echo "<div class='alert alert-$class' role='alert'>";

            foreach($tabRetour['Error'] as $error){?>
                <p><?php echo $error?></p>
            <?php }?>
            </div>
        </section>
    <?php }?>
    <footer class="footer navbar-fixed-bottom">
        <div class="col-xs-4">&copy; everydayidea.be</div>
        <div class="col-xs-4" style="text-align: center"> Contactez <a href="mailto:postmaster@everydayidea.be">l'administrateur</a></div>
        <div class="col-xs-4"></div>
    </footer>
</section>
</body>
</html>