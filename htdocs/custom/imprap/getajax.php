<?php
// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
	require '../../main.inc.php'; // From "custom" directory
}

$id = GETPOST('idclt','int');
$idfourn = GETPOST('idfourn','int');

if(!empty($id)){
	
	$sql = " SELECT * FROM " . MAIN_DB_PREFIX . "facture ";
	$sql.= " Where fk_soc = ".$id." AND fk_statut = 1";
	
	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;
		$arrayAccount = array();

		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			$arrayAccount[] = $obj;
			$i++;
		}
	}
	$db->free($resql);
	
	
	//get type paiement 
	foreach($arrayAccount as $key => $val){
		
		$sql = " SELECT sum(amount) as amount FROM llx_paiement_facture where fk_facture = ".$val->rowid;
		
		$resql = $db->query($sql);
		$obj = $db->fetch_object($resql);
		$db->free($resql);
		if($obj->amount > $val->total_ttc){
			unset($arrayAccount[$key]);
		}
	}
	
    // envoi du résultat au success
    echo json_encode($arrayAccount);
	
}else if(!empty($idfourn)){
	
	$sql = " SELECT * FROM " . MAIN_DB_PREFIX . "facture_fourn ";
	$sql.= " Where fk_soc = ".$idfourn." AND fk_statut = 1";
	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;
		$arrayAccount = array();

		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			$arrayAccount[] = $obj;
			$i++;
		}
	}
	$db->free($resql);
	
	echo json_encode($arrayAccount);
}

?>

