<?php
/**
 * Created by PhpStorm.
 * User: JulienTour
 * Date: 22/11/2015
 * Time: 20:41
 */
require "../Library/constante.lib.php";
require "../Library/Fonctions/Fonctions.php";
require "../Form/formGroupe.form.php";
require "../Form/gererActionGroupe.form.php";
initRequire();
initRequireEntityManager();
require "../Library/Page/groupe.lib.php";
require "../Manager/User_ActivityManager.manager.php";
require "../Manager/User_GroupeManager.manager.php";
require "../Entity/Groupe.class.php";
require "../Manager/GroupeManager.manager.php";
require "../Manager/Groupe_InvitationManager.manager.php";
require "../Manager/AmisManager.manager.php";
require "../Entity/Amis.class.php";
require "../Manager/Groupe_MessageManager.manager.php";

$configIni = getConfigFile();
startSession();
$user = getSessionUser();
$isConnect = isConnect();
if(!$isConnect or ($_SESSION['User']->getDroit()[0]->getLibelle() != 'Premium' and $_SESSION['User']->getDroit()[0]->getLibelle() != 'Administrateur' and $_SESSION['User']->getDroit()[0]->getLibelle() != 'Moderateur'))header("Location:../");


?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Groupe d'activité</title>
    <link rel="icon" type="image/png" href="../Images/favicon.png" />
    <link rel="stylesheet" type="text/css" href="../vendor/twitter/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../Style/general.css">

    <script src="https://code.jquery.com/jquery-2.1.4.min.js" defer></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" defer></script>
    <script src="dist/js/bootstrap-submenu.min.js" defer></script>


</head>
<body>

<section class="container" id="administration">
    <header>
        <?php include("../Menu/menuGeneral.lib.php");?>
    </header>
    <div class="col-md-2 clearfix" id="sub-menu-left">
        <nav class="sidebar-nav">
            <ul class="nav sidebar-nav sidebar-collapse">
                <li <?php if((empty($_GET))) {echo 'class="active"';}?>><a href="groupe.page.php">Voir les utilisateurs</a></li>
                <?php
                if (!hasGroupe()) {
                    echo "<li";
                    if(isset($_GET['to']) && $_GET['to'] == 'creerGroupe') {
                        echo ' class="active"';
                    }
                    echo "><a href='groupe.page.php?to=creerGroupe'>Créer votre groupe</a></li>";
                } else {
                    echo "<li";
                    if(isset($_GET['to']) && $_GET['to'] == 'voirGroupe') {
                        echo ' class="active"';
                    }
                    echo "><a href='groupe.page.php?to=voirGroupe'>Voir mon groupe</a></li>";
                }
                if (!hasGroupe()) {
                    echo "<li";
                    if(isset($_GET['to']) && $_GET['to'] == 'listeGroupe') {
                        echo ' class="active"';
                    }
                    echo "><a href='groupe.page.php?to=listeGroupe'>Voir les groupes</a></li>";
                }

                if (!hasGroupe()) {
                    echo "<li";
                    if(isset($_GET['to']) && $_GET['to'] == 'invitation') {
                        echo ' class="active"';
                    }
                    echo "><a href='groupe.page.php?to=invitation'>Voir mes invitations</a></li>";
                }

                ?>
            </ul>
        </nav>
    </div>
    <section class="col-lg-8 jumbotron">
        <h1> <img class="jumbotitre" src="../Images/bannieres/groupe.png" alt="logo" id='image-media'></h1>
        <?php
            if (!isset($_GET['to'])) {
                echo "<p class='jumbotexte'>Affichage de la liste des membres premium possédant la même activité que vous. Il est possible de les ajouter à votre groupe ou de rejoindre leur groupe </p>";
            } else if (isset($_GET['to']) && $_GET['to'] == 'creerGroupe') {
                echo "<p class='jumbotexte'>Créez votre groupe ! Vous pourrez ensuite inviter d'autres membres dedans pour leur partager votre activité ensemble </p>";
            } else if (isset($_GET['to']) && $_GET['to'] == 'listeGroupe') {
                echo "<p class='jumbotexte'>Vous pouvez choisir un groupe et le rejoindre selon vos préférences et envies </p>";
            } else if (isset($_GET['to']) && $_GET['to'] == 'ajouter') {
            echo "<p class='jumbotexte'>Vous pouvez ajouter cette personne à votre groupe, une invitation lui sera envoyée </p>";
            } else if (isset($_GET['to']) && $_GET['to'] == 'rejoindre') {
                echo "<p class='jumbotexte'>Vous pouvez rejoindre ce groupe ! Toutes vos invitations seront supprimées et vous aurez accès à la page du groupe </p>";
            } else if (isset($_GET['to']) && $_GET['to'] == 'voirGroupe') {
                echo "<p class='jumbotexte'> Vous pouvez voir les différents membres, les ajouter en ami ou discuter avec eux via la messagerie du groupe ";
            } else if (isset($_GET['to']) && $_GET['to'] == 'invitation') {
            echo "<p class='jumbotexte'> Acceptez ou refusez les invitations que les membres d'autres groupes vous ont envoyées ";
        }



        ?>
    </section>
    <section class="alert-dismissible">

    </section>
    <section class="row">
        <article class="col-sm-12">
            <?php
            if (!hasActivity()) {
                echo "<h1><div class='alert alert-warning' role='alert'> Vous n'avez pas d'activité pour le moment ! <a href='choisirCategorie.page.php'>Choississez une activité !</a></div></h1>";
            } else {
                if (!isset($_GET['to'])) {
                    afficherMembres();
                } else if (isset($_GET['to']) && $_GET['to'] == 'listeGroupe' && !hasGroupe()) {
                    listeGroupe();
                }  else if (isset($_GET['to']) && $_GET['to'] == 'creerGroupe') {
                    if (hasGroupe()) {
                        header("Location:../");
                    } else {
                        creerGroupe();
                        formCreerGroupe();
                    }

                } else if (isset($_GET['to']) && $_GET['to'] == 'ajouter' && isset($_GET['membre']) && membreExistant() && !isInGroupe($_GET['membre']) && $_GET['membre'] != $_SESSION['User']->getId()) {
                    if (isPremium() && sameActivity($_GET['membre']) && hasGroupe()) {
                        envoiInvitation();
                        formAjouter();
                    } else {
                        header("Location:groupe.page.php");
                    }
                }else if (isset($_GET['to']) && $_GET['to'] == 'rejoindre' && isset($_GET['groupe']) && !hasGroupe() && groupeExiste() && groupeSameActivity()) {
                    if (isset($_POST['AccepterRejoindre']) || isset($_POST['RefuserRejoindre'])) {
                        rejoindreGroupe();
                    } else {
                        formRejoindreGroupe();
                    }
                } else if (isset($_GET['to']) && $_GET['to'] == 'voirGroupe' && isset($_GET['action']) && $_GET['action'] == 'mod') {
                    gererActionGroupe();
                    gererReponseGroupe();
                } else if (isset($_GET['to']) && $_GET['to'] == 'voirGroupe' && hasGroupe()) {
                    if (gererActionMembre()) {
                    } else if (isset($_POST['Acceptersupprimer']) || isset($_POST['Refusersupprimer'])) {
                        supprimerMembre();
                    } else if (isset($_POST['Accepterlead']) || isset($_POST['Refuserlead'])) {
                        nommerLeader();
                    } else {
                        envoiMessage();
                        voirGroupe();
                    }
                } else if (isset($_GET['to']) && $_GET['to'] == 'invitation' && !hasGroupe()) {
                    gererReponseInvitation();
                    afficherInvitation();
                } else {
                    header("Location:groupe.page.php");
                }
            }
            ?>
        </article>
    </section>
    <footer class="footer navbar-fixed-bottom">
        <div class="col-xs-4">&copy; everydayidea.be</div>
        <div class="col-xs-4" style="text-align: center"> Contactez <a href="mailto:postmaster@everydayidea.be">l'administrateur</a></div>
        <div class="col-xs-4"></div>
    </footer>
</section>

</body>