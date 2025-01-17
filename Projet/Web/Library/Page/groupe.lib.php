<?php
/**
 * Created by PhpStorm.
 * User: JulienTour
 * Date: 22/11/2015
 * Time: 20:41
 */
use \Entity\Groupe as Groupe;
use \Entity\User as User;

/**
 * Fonction permettant d'afficher la liste des membres premium ayant la même activité que moi. Cela indique aussi
 * si ils ont un groupe ou non.
 */
function afficherMembres() {
    $um = new UserManager(connexionDb());
    $uam = new User_ActivityManager(connexionDb());
    $ugm = new User_GroupeManager(connexionDb());
    $act = $uam->getActIdByUserId($_SESSION['User']);
    $groupeUser = $ugm->getGroupeIdByUserId($_SESSION['User']);
    $tab = $um ->getAllUser();
    $lenght = count($tab);
    $existant = false;
    ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <caption> <h2> Membres premium ayant la même activité que vous</h2></caption>
            <tr>
                <th> Nom d'utilisateur</th>
                <th> Dernière connexion</th>
                <th> Date d'inscription</th>
                <th> Action</th>
            </tr>
            <?php
            foreach ($tab as $elem) {
                #TODO TEST QUAND LA BASE DE DONNEES SERA CLEAN CAR BUG
                $id = $elem->getId();
                $actUser = $uam->getActIdByUserId($elem);
                if (isset($id) && $id != $_SESSION['User']->getId()) {
                        if (isset($elem->getDroit()[0]) && ($elem->getDroit()[0]->getId() == 3 || $elem->getDroit()[0]->getId() == 2 || $elem->getDroit()[0]->getId() == 1)) {
                            if ($actUser != NULL) {
                                if ($actUser[0]['id_activity'] == $act[0]['id_activity']) {
                                    $groupe = $ugm->getGroupeIdByUserId($elem);
                                    echo "<tr> <td>" . $elem->getUserName() . " </td><td>" . $elem->getDateLastConnect() . "</td><td>" . $elem->getDateInscription() . "</td>";
                                    if ($groupeUser != NULL && dejaInvite($id, $groupeUser[0]['id_groupe'])) {
                                        echo "<td> Cette personne a déjà été invitée dans votre groupe !</td>";
                                    } else if ($groupeUser != NULL && sameGroupe($elem, $groupeUser[0]['id_groupe'])) {
                                        echo "<td> Cette personne est déjà dans votre groupe !</td>";
                                    } else if ($groupe != NULL && hasGroupe()) {
                                        echo "<td> Cette personne est déjà dans un groupe, tout comme vous !</td>";
                                    } else if ($groupe != NULL) {
                                        echo "<td><a href='groupe.page.php?to=rejoindre&groupe=" . $groupe[0]['id_groupe'] . "'> Rejoindre le groupe </a></td>";
                                    } else if (hasGroupe()) {
                                        echo "<td><a href='groupe.page.php?to=ajouter&membre=$id'> Ajouter dans mon groupe </a></td>";
                                    } else {
                                        echo "<td> Vous n'avez pas de groupe, tout comme la personne !</td>";
                                    }
                                    echo "<tr>";
                                    $existant = true;

                                }


                            }


                        }
                }
            }

            if ($tab == NULL || !$existant) {
                echo "<tr> <td> Aucun utilisateur trouvé !</td></tr>";
            }
            ?>
        </table>
    </div>
    <?php

}

/**
 * Fonction permettant d'afficher les demandes de groupe que j'ai reçue de la part d'autres membres.
 *
 *
 * */
function afficherInvitation() {
    $gim = new Groupe_InvitationManager(connexionDb());
    $invit = $gim->getInvitationByDemande($_SESSION['User']);
    $um = new UserManager(connexionDb());
    $gm = new GroupeManager(connexionDb());
    $existe = true;
    ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <caption> <h2> Invitations de groupe </h2></caption>
                <tr>
                    <th> Utilisateur envoyant l'invitation</th>
                    <th> Description du groupe </th>
                    <th> Validation </th>
                </tr>
                <?php
                foreach ($invit as $elem) {
                    $user = $um->getUserById($elem['id_user_envoi']);
                    $groupe = $gm->getGroupeByIdGroupe($elem['id_groupe']);
                    $id = $groupe->getIdGroupe();
                    echo "<tr> <td>" . $user->getUserName() . " </td><td>" . substr($groupe->getDescription(),0,80)."..." . " </td><td>";
                    echo "<form class='form-horizontal col-sm-12' name='accepter$id' action='groupe.page.php?to=invitation&action=gerer' method='post'>";
                    echo "<button class='btn btn-success col-sm-6' type='submit' id='formulaire' name='AccepterGroupe$id'>Accepter</button>";
                    echo "<button class='btn btn-warning col-sm-6' type='submit' id='formulaire' name='RefuserGroupe$id'>Refuser</button>";
                    echo "<input type='hidden'  name='groupe$id'  value='" . $id . "''>";
                    echo "</form>";
                    echo "</td></tr>";
                    $existe = false;
                    }

                if ($invit == NULL || $existe) {
                    echo "<tr> <td> Aucune invitation reçue !</td></tr>";
                }
                ?>
            </table>
        </div>
        <?php

}

/**
 * Fonction permettant de gérer la réponse donnée au formulaire du groupe auquel on appartient. Soit on modifie le groupe,
 * soit on le supprime, soit on le quitte.
 */
function gererReponseGroupe() {
    if (isset($_POST['modifier'])) {
        include "../Form/modifierDescription.form.php";
    } else if (isset($_POST['delete'])) {
       include "../Form/formDeleteGroupe.form.php";
    } else if (isset($_POST['leave'])) {
        include "../Form/formLeaveGroupe.form.php";
    }
}

/**
 * Fonction effectuant l'action voulue par le membre concernant le groupe auqeul il appartient. Soit on supprime le groupe,
 * soit on le quitte, soit on modifie sa description.
 */
function gererActionGroupe() {
    $gm = new GroupeManager(connexionDb());
    $ugm = new User_GroupeManager(connexionDb());
    $gmm = new Groupe_MessageManager(connexionDb());
    if (isset($_POST['Refuser'])) {
        header("Location:groupe.page.php?to=voirGroupe");
    } else if (isset($_POST['AccepterSupprimer'])) {
        $groupe = $gm->getGroupeByLeader($_SESSION['User']);
        $ugm->deleteGroupe($groupe);
        $gmm->deleteMessByGroupe($groupe);
        $gm->deleteGroupe($_SESSION['User']->getId());
        header("Location:groupe.page.php");
    } else if (isset($_POST['AccepterLeave'])) {
        $ugm->deleteUserGroupe($_SESSION['User']);
        header("Location:groupe.page.php");
    } else if (isset($_POST['modifierDesc'])) {
        $groupe = $gm->getGroupeByLeader($_SESSION['User']);
        $gm->updateGroupeDesc($groupe, $_POST['descriptionGroupe']);
        header("Location:groupe.page.php?to=voirGroupe");
    }
}

/**
 * Fonction permettant l'affichage du groupe auquel on appartient ainsi que la liste des membre du groupe et le chat du groupe.
 */
function voirGroupe() {
    $ugm = new User_GroupeManager(connexionDb());
    $groupeId = $ugm->getGroupeIdByUserId($_SESSION['User']);
    $gm = new GroupeManager(connexionDb());
    $am = new ActivityManager(connexionDb());
    $um = new UserManager(connexionDb());
    $amiM = new AmisManager(connexionDb());
    $gmm = new Groupe_MessageManager(connexionDb());
    $groupe = $gm->getGroupeByIdGroupe($groupeId[0]['id_groupe']);
    $leader = $um->getUserById($groupe->getIdLeader());
    $act = $am->getActivityById($groupe->getIdActivity());
    $membres = $ugm->getUserIdByGroupeId($groupe);
    $messages = $gmm->getMessageByGroup($groupe);
    $existe = false;
    $autreMembre = false;
    formGroupe($groupe);
    echo "<div class='titleGroupe'>";
    echo "<div class='photoGroupe'>";
    echo "<img class='photoAct' src='../Images/activite/".$act->getId().".jpg' alt='photoActivite' />";
    echo "</div>";
    echo "<h1 align='center'>".$act->getLibelle()."</h1>";
    echo "<h2 align='center'> Chef de groupe : ".$leader->getUserName()."</h2>";
    echo "</div>";
    echo "<h3 align='center'> Description de votre activité :</h3>";

    echo "<div class='well well-lg'><h4 align='center'>".$act->getDescription() ."</h4></div>";
    echo "<h3 align='center'> Description de votre groupe : </h3>";

        echo "<div class='well well-lg'><h3 align='center'>" . $groupe->getDescription() . " </h3></div>";

    ?>
    <div class="table-responsive">
    <table class="table table-striped">
        <caption> <h2> Membres du groupe </h2></caption>
        <tr>
            <th> Utilisateur </th>
            <th> Date de dernière connexion</th>
            <th> Ajouter en ami </th>
            <?php if ($groupe->getIdLeader() == $_SESSION['User']->getId()) {?>
                <th> Supprimer du groupe </th>
                <th> Nommer chef de groupe </th>
            <?php } ?>
        </tr>
        <?php
        foreach ($membres as $elem) {
            $user = $um->getUserById($elem['id_user']);
            $id = $user->getId();
            if ($user->getId() != $_SESSION['User']->getId()) {
                $autreMembre = true;
                $amiTest1 = $amiM->getAmisById1AndId2($user->getId(), $_SESSION['User']->getId());
                $amiTest2 = $amiM->getAmisById1AndId2($_SESSION['User']->getId(), $user->getId());
                echo "<tr> <td>" . $user->getUserName() . " </td><td>" . $user->getDateLastConnect() . " </td><td>";
                if ($amiTest1->getIdUser1() == NULL && $amiTest2->getIdUser1() == NULL) {
                    echo "<a href='demandeAmi.page.php?membre=" . $user->getId() . "'> Ajouter comme ami </a>";
                } else {
                    echo "Vous êtes déjà ami avec cette personne !";
                }
                echo "</td>";
                if ($groupe->getIdLeader() == $_SESSION['User']->getId()) {
                    echo "<td><form class='form-horizontal col-sm-12' name='suppression$id' action='groupe.page.php?to=voirGroupe' method='post'>";
                    echo "<input type='hidden'  name='idMembre$id'  value='" . $id . "''>";
                    echo "<button class='btn btn-danger col-sm-10' type='submit' id='formulaire' name='supprimerMembre$id'>Supprimer ce membre</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "<td><form class='form-horizontal col-sm-12' name='nommerLead$id' action='groupe.page.php?to=voirGroupe' method='post'>";
                    echo "<input type='hidden'  name='idMembre$id'  value='" . $id . "''>";
                    echo "<button class='btn btn-success col-sm-10' type='submit' id='formulaire' name='nommerLead$id'>Nommer chef de groupe</button>";
                    echo "</form>";
                    echo "</td>";
                }
                echo "</tr>";
            }
        }
        if (!$autreMembre) {
            echo "<tr> <td> Vous êtes le seul membre du groupe pour le moment !</td></tr>";
        }
        ?>
    </table>
    </div>
    <?php
    echo "<br> <br>";
    echo "<h2> Messagerie du groupe : </h2><br>";
    echo "<div class='messagerieGroupe'>";
    include "../Form/groupeMessage.form.php";
    echo "<br>";
    ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <tr>
                <th> Utilisateur</th>
                <th> Date </th>
                <th> Message </th>
            </tr>
            <?php
            foreach ($messages as $elem) {
                $user = $um->getUserById($elem['id_user']);
                echo "<tr> <td>" . $user->getUserName() . " </td><td>" . $elem['date'] . " </td><td>";
                echo $elem['description'];
                echo "</td></tr>";
                $existe = false;
            }

            if ($messages == NULL || $existe) {
                echo "<tr> <td> Aucun message pour le moment !</td></tr>";
            }
            ?>
        </table>
    </div>
    <?php
    echo "</div>";

}

/**
 * Fonction affichant la liste des groupes existant pour cette activité.
 */
function listeGroupe() {
    $gm = new GroupeManager(connexionDb());

    $um = new UserManager(connexionDb());
    $uam = new User_ActivityManager(connexionDb());
    $act = $uam->getActIdByUserId($_SESSION['User']);
    $tabGroupe = $gm->getAllGroupeByAct($act[0]['id_activity']);
    $existe = false;
    ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <caption> <h2> Liste des groupes </h2></caption>
            <tr>
                <th> Description de groupe </th>
                <th> Chef du groupe </th>
                <th> Rejoindre le groupe </th>
            </tr>
            <?php
            foreach ($tabGroupe as $elem) {
                $user = $um->getUserById($elem->getIdLeader());
                $id = $elem->getIdGroupe();
                echo "<tr> <td>" . substr($elem->getDescription(),0,120)."..." . " </td><td>" . $user->getUserName() . " </td>";
                echo "<td><a href='groupe.page.php?to=rejoindre&groupe=" . $elem->getIdGroupe() . "'> Rejoindre le groupe </a></td>";
                echo "</tr>";
                $existe = true;
            }

            if ($tabGroupe == NULL || !$existe) {
                echo "<tr> <td> Aucun groupe pour le moment !</td></tr>";
            }
            ?>
        </table>
    </div>
    <?php
}


/**
 * Fonction gérant l'action faite par le leader du groupe sur un des autres membres du groupe. Soit il le vire du groupe,
 * soit il le nomme leader.
 * @return bool : true si tout se passe bien, false si il y a une erreur.
 */
function gererActionMembre() {
    $um = new UserManager(connexionDb());
    $tabUser = $um->getAllUser();
    $trueId = 0;
    foreach ($tabUser as $elem) {
        $id = $elem->getId();
        if (isset($_POST['supprimerMembre'.$id.''])){
            formGererActionGroupe('supprimer', $id);
            return true;
        }
        if (isset($_POST['nommerLead'.$id.''])) {
            formGererActionGroupe('lead', $id);
            return true;
        }
    }
    return false;
}

/**
 * Fonction modifiant le leader d'un groupe.
 */
function nommerLeader() {
    if (isset($_POST['Accepterlead'])) {
        $id = $_POST['membre'];
        $gm = new GroupeManager(connexionDb());
        $groupe = $gm->getGroupeByLeader($_SESSION['User']);
        $gm->updateLeader($groupe, $id);
        header("Location:groupe.page.php?to=voirGroupe");
    } else if (isset($_POST['Refuserlead'])) {
        header("Location:groupe.page.php?to=voirGroupe");
    }
}

/**
 * Fonction supprimant le membre d'un groupe.
 */
function supprimerMembre()
{
    if (isset($_POST['Acceptersupprimer'])) {
        $id = $_POST['membre'];
        $ugm = new User_GroupeManager(connexionDb());
        $userToDelete = new User(array(
            "id" => $id,
        ));
        $ugm->deleteUserGroupe($userToDelete);
        header("Location:groupe.page.php?to=voirGroupe");
    } else if (isset($_POST['Refusersupprimer'])) {
        header("Location:groupe.page.php?to=voirGroupe");
    }
}

/**
 * Fonction envoyant un message dans le chat du groupe.
 */
function envoiMessage() {
    if (isset($_POST['poster'])) {
        if (champsTexteValable($_POST['description'])) {
            if (strlen($_POST['description']) >= 2) {
                $gmm = new Groupe_MessageManager(connexionDb());
                $ugm = new User_GroupeManager(connexionDb());
                $groupeId = $ugm->getGroupeIdByUserId($_SESSION['User']);
                $groupe = new Groupe(array(
                    "id_groupe" => $groupeId[0]['id_groupe'],
                ));
                $gmm->addMess($groupe, $_SESSION['User'], $_POST['description']);
                header("Location:groupe.page.php?to=voirGroupe");
            } else {
                echo "<h1 align='center'><div class='alert alert-danger' role='alert'> Votre message est trop court !  </div></h1>";
            }
        } else {
            echo "<h1 align='center'><div class='alert alert-danger' role='alert'> Votre message contient des caractères indésirables !  </div></h1>";
        }
    }

}

/**
 * Fonction permettant de gérer l'action du membre face aux différentes invitations de groupes.
 * @return int : l'id du groupe concerné par l'action du membre.
 */
function gererFormInvitation() {
    $gm = new GroupeManager(connexionDb());
    $tabGroupe = $gm->getAllGroupe();
    $trueId = 0;
    foreach ($tabGroupe as $elem) {
        $id = $elem->getIdGroupe();
        if (isset($_POST['AccepterGroupe'.$id.''])){
            $trueId = $id;
        } else if (isset($_POST['RefuserGroupe'.$id.''])) {
            $trueId = $id;
        }
    }
    return $trueId;
}

/**
 * Fonction permettant de gérer l'action du membre face aux différentes invitations de groupes. Soit il rejoint le groupe
 * et les autres invitations sont supprimées, soit il refuse l'invitation et elle est supprimée.
 */
function gererReponseInvitation() {
    $id = gererFormInvitation();
    if (isset($_POST['AccepterGroupe'.$id.'']) || isset($_POST['RefuserGroupe'.$id.''])) {
        $gim = new Groupe_InvitationManager(connexionDb());
        $ugm = new User_GroupeManager(connexionDb());
        $groupe = new Groupe(array(
            "id_groupe" => $id,
        ));


        if (isset($_POST['AccepterGroupe'.$id.''])) {
            $ugm->addToUserGroupe($_SESSION['User'], $groupe);
            $gim->deleteInvitByUserId($_SESSION['User']);
            header("Location:groupe.page.php?to=voirGroupe");
        } else if (isset($_POST['RefuserGroupe'.$id.''])) {
            $gim->deleteInvitByGroupeIdAndUserId($groupe, $_SESSION['User']);
            header("Location:groupe.page.php?to=invitation");
        }
    }
}

/**
 * Fonction permettant de savoir si le membre connecté possède une activité.
 * @return bool : true si il en possède une, false sinon.
 */
function hasActivity() {
    $uam = new User_ActivityManager(connexionDb());
    $act = $uam->getActIdByUserId($_SESSION['User']);
    if ($act == NULL) {
        return false;
    } else {
        return true;
    }

}

/**
 * Fonction permettant de savoir si le membre connecté possède un groupe.
 * @return bool : true si il en possède un, false sinon.
 */
function hasGroupe() {
    $ugm = new User_GroupeManager(connexionDb());
    $groupe = $ugm->getGroupeIdByUserId($_SESSION['User']);
    if ($groupe == NULL) {
        return false;
    } else {
        return true;
    }
}

/**
 * Fonction permettant de savoir si un autre membre de soi est déjà dans un groupe.
 * @param $id : l'id du membre concerné.
 * @return bool : true si il est dans un groupe, false sinon.
 */
function isInGroupe($id) {
    $ugm = new User_GroupeManager(connexionDb());
    $user = new User(array(
        "id" => $id,
    ));
    $groupe = $ugm->getGroupeIdByUserId($user);
    if ($groupe == NULL) {
        return false;
    } else {
        return true;
    }
}

/**
 * Fonction générant le formulaire de création de groupe.
 */
function formCreerGroupe() {
    include "../Form/creerGroupe.form.php";
}

/**
 * Fonction générant le formulaire permettant de rejoindre un groupe.
 */
function formRejoindreGroupe() {
    include "../Form/rejoindreGroupe.form.php";
}

/**
 * Fonction permettant de générer le formulaire d'invitation de membre dans son groupe.
 */
function formAjouter() {
    $id = $_GET['membre'];
    $ugm = new User_GroupeManager(connexionDb());
    $groupe = $ugm->getGroupeIdByUserId($_SESSION['User']);
    if (!dejaInvite($id, $groupe[0]['id_groupe'])) {
        include "../Form/demandeGroupe.form.php";
    } else {
        header("Location:groupe.page.php");
    }
}

/**
 * Fonction permettant d'envoyer une invitation à un membre pour rejoindre son groupe.
 */
function envoiInvitation() {
    if (isset($_POST['Accepter']) || isset($_POST['Refuser'])) {
        $id = $_GET['membre'];
        $ugm = new User_GroupeManager(connexionDb());
        $groupe = $ugm->getGroupeIdByUserId($_SESSION['User']);
        $gim = new Groupe_InvitationManager(connexionDb());
            if (isset($_POST['Accepter'])) {
                $gim->addInvit($id, $_SESSION['User']->getId(), $groupe[0]['id_groupe']);
                echo "<h1 align='center'><div class='alert alert-success' role='alert'> Votre demande a bien été envoyée !  </div></h1>";
                echo "<meta http-equiv='refresh' content='2; URL=groupe.page.php'>";
            } else if (isset($_POST['Refuser'])) {
                header("Location:groupe.page.php");
            }

    }
}

/**
 * Fonction vérifiant si un membre concerné est premium.
 * @return bool : true si il est premium, false sinon.
 */
function isPremium() {
    $id = $_GET['membre'];
    $um = new UserManager(connexionDb());
    $userDroit = $um->getUserById($id);
    if ($userDroit->getDroit()[0]->getLibelle() == 'Premium' || $userDroit->getDroit()[0]->getLibelle() == 'Administrateur' || $userDroit->getDroit()[0]->getLibelle() == 'Moderateur') {
        return true;
    } else {
        return false;
    }
}

/**
 * Fonction permettant de savoir si le membre connecté et un autre sont dans le même groupe.
 * @param User $user : le membre concerné.
 * @param $idGroupe : l'id du groupe.
 * @return bool : true si ils sont dans le même groupe, false sinon.
 */
function sameGroupe(User $user, $idGroupe) {
    $ugm = new User_GroupeManager(connexionDb());
    $groupe = $ugm->getGroupeIdByUserId($user);
    if ($groupe != NULL && $groupe[0]['id_groupe'] == $idGroupe) {
        return true;
    } else {
        return false;
    }
}

/**
 * Fonction permettant de vérifier si l'id du groupe contenue dans l'url est celle d'un groupe existant.
 * @return bool : true si le groupe existe, false sinon.
 */
function groupeExiste() {
    $id=$_GET['groupe'];
    $gm = new GroupeManager(connexionDb());
    $groupe = $gm->getGroupeByIdGroupe($id);
    if ($groupe->getDescription() == NULL) {
        return false;
    } else {
        return true;
    }

}

/**
 * Fonction permettant de savoir si l'id du groupe contenue dans l'url est celle d'un groupe concernant la même activité
 * que le membre connecté.
 * @return bool : true si le groupe possède la même activité, false sinon.
 */
function groupeSameActivity() {
    $id = $_GET['groupe'];
    $gm = new GroupeManager(connexionDb());
    $groupe = $gm->getGroupeByIdGroupe($id);
    $uam = new User_ActivityManager(connexionDb());
    $act = $uam->getActIdByUserId($_SESSION['User']);
    if ($act[0]['id_activity'] == $groupe->getIdActivity()) {
        return true;
    } else {
        return false;
    }
}

/**
 * Fonction permettant de savoir si le membre connecté et un autre ont la même activité.
 * @param $idUser : id du membre concerné.
 * @return bool : true si ils ont la même activité, false sinon.
 */
function sameActivity($idUser) {
    $uam = new User_ActivityManager(connexionDb());
    $user = new User(array(
        "id" => $idUser,
    ));
    $activityUser = $uam->getActIdByUserId($user);
    $activityToCompare = $uam->getActIdByUserId($_SESSION['User']);
    if ($activityUser[0]['id_activity'] == $activityToCompare[0]['id_activity']) {
        return true;
    } else {
        return false;
    }
}

/**
 * Fonction permettant de savoir si un membre a déjà été invité dans son groupe.
 * @param $idUser : id du membre concerné.
 * @param $idGroupe : id du groupe concerné.
 * @return bool : true si il a déjà été invité, false sinon.
 */
function dejaInvite($idUser, $idGroupe) {
    $gim = new Groupe_InvitationManager(connexionDb());
    $existe = false;
    $groupe = new Groupe(array(
        "id_groupe" => $idGroupe,
    ));
    $invit = $gim->getInvitationByGroupe($groupe);
    foreach ($invit as $elem) {
        if ($elem['id_user_demande'] == $idUser) {
            $existe = true;
        }
    }
    if ($existe) {
        return true;
    } else {
        return false;
    }
}

/**
 * Fonction permettant de rejoindre un groupe.
 */
function rejoindreGroupe() {
    if (isset($_POST['AccepterRejoindre']) || isset($_POST['RefuserRejoindre'])) {
        $id = $_GET['groupe'];
        $groupe = new Groupe(array(
            "id_groupe" => $id,
        ));
        $gim = new Groupe_InvitationManager(connexionDb());
        $ugm = new User_GroupeManager(connexionDb());

        if (isset($_POST['AccepterRejoindre'])) {
            $gim->deleteInvitByUserId($_SESSION['User']);
            $ugm->addToUserGroupe($_SESSION['User'], $groupe);
            echo "<h1 align='center'><div class='alert alert-success' role='alert'> Vous avez bien rejoint le groupe !  </div></h1>";
            echo "<meta http-equiv='refresh' content='2; URL=groupe.page.php?to=voirGroupe'>";
        } else if (isset($_POST['RefuserRejoindre'])) {
            header("Location:groupe.page.php");
        }

    }
}

/**
 * Fonction permettant de créer un groupe.
 */
function creerGroupe() {
    if (isset($_POST['formulaireCreation'])) {
        $desc = $_POST['description'];
        if (champsTexteValable($desc)) {
            $groupe = new Groupe(array(
                "id_leader" => $_SESSION['User']->getId(),
                "description" => $_POST['description'],
                "id_activity" => $_POST['idAct'],
            ));
            $gm = new GroupeManager(connexionDb());
            $gim = new Groupe_InvitationManager(connexionDb());
            $gim->deleteInvitByUserId($_SESSION['User']);
            $ugm = new User_GroupeManager(connexionDb());
            $gm->addGroupe($groupe);
            $groupeLead = $gm->getGroupeByLeader($_SESSION['User']);
            $ugm->addToUserGroupe($_SESSION['User'], $groupeLead);
            echo "<h1 align='center'><div class='alert alert-success' role='alert'> Le groupe a bien été créé !  </div></h1>";
            echo "<meta http-equiv='refresh' content='2; URL=groupe.page.php?to=voirGroupe'>";
        } else {
            echo "<h1 align='center'><div class='alert alert-danger' role='alert'> Votre description contient des caractères indésirables !  </div></h1>";
        }
    }
}