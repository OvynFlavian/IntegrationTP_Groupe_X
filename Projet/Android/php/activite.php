<?php
	define('HOST','localhost');
	define('USER','root');
	define('PASS','totopipo007');
	define('DB','projet');

	$con = mysqli_connect(HOST,USER,PASS,DB);

	$categorie = $_POST['categorie'];

	$sql = "select id from categorie where libelle = '".$categorie."'";
	$res = mysqli_query($con,$sql);
	$rows = mysqli_fetch_array($res);
	$idCat = $rows['id'];

	$activite['idCat'] = $idCat;
	$sql = "select id, Libelle, description, note from activity where id in (select id_activity from categorie_activity where id_categorie = '".$idCat."')";
	$res = mysqli_query($con,$sql);

	$num = mysqli_num_rows($res);

	$i = 0;
	while ($rows = mysqli_fetch_array($res)) {
		$tbActivity[$i]['id'] = $rows['id'];
		$tbActivity[$i]['libelle'] = $rows['Libelle'];
		$tbActivity[$i]['description'] = $rows['description'];
		$tbActivity[$i]['note'] = $rows['note'];
		$i++;
	}
	$s = 0;
	$c = sizeof($tbActivity)-1;
	$idx=mt_rand($s, $c);
	$activite['id'] = $tbActivity[$idx]['id'];
	$activite['titre'] = $tbActivity[$idx]['libelle'];
	$activite['description'] = $tbActivity[$idx]['description'];
	if ($tbActivity[$idx]['note'] == null) {
		$activite['note'] = "99";
	} else {
		$activite['note'] = $tbActivity[$idx]['note'];
	}
	
	mysqli_free_result($res);
	
	echo json_encode($activite);

?>
