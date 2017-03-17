<?php
// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
	require '../../main.inc.php'; // From "custom" directory
}

//tous les variable
$typeG		= GETPOST('typeG');
$idpaiment  = GETPOST('idpaiment');
$idfactura  = GETPOST('idfactura');
$dateo 		= GETPOST('dateo');
$datev 		= GETPOST('datev');
$desct 		= GETPOST('desct');
$debit 		= GETPOST('debit','float');
$crdit 		= GETPOST('crdit','float');
$idAccount 	= GETPOST('idAccount');
$idclient 	= GETPOST('idclient');
$amount  	= $debit + $crdit ;


//get type paiement 
$sql = " SELECT `code` FROM " . MAIN_DB_PREFIX . "c_paiement ";
$sql.= " Where id = ".$idpaiment;
$resql = $db->query($sql);
$obj = $db->fetch_object($resql);
$db->free($resql);
$codeP = $obj->code;


//Add new ecritur in table bank et return id
$sql = " INSERT INTO `llx_bank`(`rowid`, `datec`, `tms`, `datev`, `dateo`, `amount`, `label`, `fk_account`, `fk_user_author`, `fk_user_rappro`, `fk_type`, `num_releve`, `num_chq`, `rappro`, `note`, `fk_bordereau`, `banque`, `emetteur`, `author`)" ;
$sql.= " VALUES ('',NOW(),NOW(),'".$datev."','".$dateo."',".$amount.",'(CustomerInvoicePayment)',".$idAccount.",1,'','".$codeP."','','','','','','','','')";
$resql = $db->query($sql);
$ide = $db->last_insert_id(MAIN_DB_PREFIX . "bank");


//Add new paiment in table paiment
$sql = " SELECT rowid FROM llx_paiement ORDER BY rowid DESC LIMIT 1";
$resql = $db->query($sql);
$objlastselect = $db->fetch_object($resql);
$db->free($resql);
$idp = $objlastselect->rowid + 1;

$year =  date("y");
$month =  date("m");
$year = substr( $year, -2);
$idpc = str_pad($idp, 4, '0', STR_PAD_LEFT);
$idpc = 'PAY'.$year.$month.'-'.$idpc;

$sql = " INSERT INTO `llx_paiement`(`rowid`, `ref`, `entity`, `datec`, `tms`, `datep`, `amount`, `multicurrency_amount`, `fk_paiement`, `num_paiement`, `note`, `fk_bank`, `fk_user_creat`, `fk_user_modif`, `statut`, `fk_export_compta`) ";
$sql.= " VALUES (".$idp.",'".$idpc."',1,NOW(),NOW(),NOW(),".$amount.",".$amount.",".$idpaiment.",'','',".$ide.",1,'',0,0)";
$resql = $db->query($sql);


//Add new relation paiment avec facture in table paiment_facture
$sql = " INSERT INTO `llx_paiement_facture`(`rowid`, `fk_paiement`, `fk_facture`, `amount`, `multicurrency_code`, `multicurrency_tx`, `multicurrency_amount`) ";
$sql.= " VALUES ('',".$idp.",".$idfactura.",".$amount.",'',1,".$amount.")";
$resql = $db->query($sql);


//Ajouter url bank
$sql = " INSERT INTO `llx_bank_url`( `fk_bank`, `url_id`, `url`, `label`, `type`) ";
$sql.= " VALUES (".$ide.",".$idp.",'','(paiement)','payment')";
$resql = $db->query($sql);
$sql = " INSERT INTO `llx_bank_url`( `fk_bank`, `url_id`, `url`, `label`, `type`) ";
$sql.= " VALUES (".$ide.",".$idclient.",'','(Client)','company')";
$resql = $db->query($sql);


echo json_encode($langs->trans("success"));