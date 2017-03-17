<?php
// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
	require '../../main.inc.php'; // From "custom" directory
}

$id = GETPOST('id','int');

if(isset($_GET['id'])){
	
	$sql = " SELECT * FROM " . MAIN_DB_PREFIX . "facture ";
	$sql.= " Where fk_soc = ".$id." AND facnumber LIKE 'FA%'";
			
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
