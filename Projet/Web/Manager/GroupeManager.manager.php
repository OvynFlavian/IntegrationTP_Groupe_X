<?php
/**
 * Created by PhpStorm.
 * User: JulienTour
 * Date: 22/11/2015
 * Time: 22:30
 */
use \Entity\Groupe as Groupe;
use \Entity\User as User;

class GroupeManager {
    private $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    public function getGroupeByLeader(User $user) {
        $resultats = $this->db->prepare("SELECT * FROM groupe WHERE id_leader = :id");
        $resultats->execute(array(
            ":id" => $user->getId(),
        ));

        if ($tabGroupe = $resultats->fetch(PDO::FETCH_ASSOC)) {
            $groupe = new Groupe($tabGroupe);
        } else {
            $groupe = new Groupe(array());
        }

        return $groupe;

    }

    public function getGroupeByIdActivity($id) {
        $resultats = $this->db->prepare("SELECT * FROM groupe WHERE id_activity = :id");
        $resultats->execute(array(
            ":id" => $id,
        ));

        $tabGroupe = $resultats->fetchAll(PDO::FETCH_ASSOC);

        $tab = array();

        foreach($tabGroupe as $elem)
        {
            $tab[] = new Groupe($elem);
        }

        return $tab;

    }
    public function addGroupe(Groupe $groupe)
    {
        $query = $this
            ->db
            ->prepare("INSERT INTO groupe(id_leader, date, description, id_activity) VALUES (:idLeader , NOW(), :desc, :idAct)");

        $query->execute(array(
            ":idLeader" => $groupe->getIdLeader(),
            ":desc" => $groupe->getDescription(),
            ":idAct" => $groupe->getIdActivity(),
        ));
    }

    public function deleteGroupe($idUser) {
        $query = $this
            ->db
            ->prepare("DELETE FROM groupe where id_leader = :id");

        $query->execute(array(
            ":id" => $idUser,

        ));
    }
}