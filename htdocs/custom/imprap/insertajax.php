<?php
// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
	require '../../main.inc.php'; // From "custom" directory
}
//tous les variable
$typeG		= "facture";
$ligne 		= GETPOST('row');
$idpaiment  = GETPOST('paiementtype');
$idsoc  = GETPOST('idsoc');


//get type paiement 
$sql = " SELECT * FROM " . MAIN_DB_PREFIX . "bank_rapprofile ";
$sql.= " Where rowid = ".$ligne;
$resql = $db->query($sql);
$obj = $db->fetch_object($resql);
$db->free($resql);

$dateo 		= $obj->date_d_operation;
$datev 		= $obj->date_de_valeur;
$desct 		= $obj->libelle;
$debit 		= $obj->debut;
$crdit 		= $obj->credit;
$idAccount 	= $obj->fk_account;

$error = '';

$idfourn 	= 0;
$idclient 	= 0;


if($crdit == 0){
	$idfourn 	= $idsoc;
}else{
	$idclient 	= $idsoc;
}

if(empty($idpaiment)){
	$error = $langs->trans("addCetegoreiPaiment");
	header("Location: treatment.php?errorinsert=".$error);
	exit;
}
if(empty($idsoc)){
	$error = $langs->trans("addTierOrProv");
	header("Location: treatment.php?errorinsert=".$error);
	exit;
}

if($typeG == 'facture'){
	if(isset($_POST['montant'])){
		$montantF 	= GETPOST('montant');
		$rowidF 	= GETPOST('rowidmontant');
		
		$amount  	= $debit + $crdit ;
		$compteMontant = 0;

		foreach($montantF as $key => $val){
			if(empty(trim($val))){
				unset($montantF[$key]);
				unset($rowidF[$key]);
			}else{
				$compteMontant = $compteMontant + $val;
			}
		}

		if(empty($montantF)){
			
			$error = $langs->trans("addMontant");
			header("Location: treatment.php?errorinsert=".$error);
			exit;
			
		}elseif($compteMontant != $amount){
			
			$error = $langs->trans("lesMantantInvalid");
			header("Location: treatment.php?errorinsert=".$error);
			exit;
			
		}else{

			if($idclient != 0){
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
				foreach($montantF as $key => $val){
					$idfactura = $rowidF[$key];
					$sql = " INSERT INTO `llx_paiement_facture`(`rowid`, `fk_paiement`, `fk_facture`, `amount`, `multicurrency_code`, `multicurrency_tx`, `multicurrency_amount`) ";
					$sql.= " VALUES ('',".$idp.",".$idfactura.",".$val.",'',1,".$val.")";
					$resql = $db->query($sql);
				}


				//Ajouter url bank
				$sql = " INSERT INTO `llx_bank_url`( `fk_bank`, `url_id`, `url`, `label`, `type`) ";
				$sql.= " VALUES (".$ide.",".$idp.",'','(paiement)','payment')";
				$resql = $db->query($sql);
				$sql = " INSERT INTO `llx_bank_url`( `fk_bank`, `url_id`, `url`, `label`, `type`) ";
				$sql.= " VALUES (".$ide.",".$idclient.",'','(Client)','company')";
				$resql = $db->query($sql);

				header("Location: treatment.php");
				exit;
				
			}else{
				
				//get type paiement 
				$sql = " SELECT `code` FROM " . MAIN_DB_PREFIX . "c_paiement ";
				$sql.= " Where id = ".$idpaiment;
				$resql = $db->query($sql);
				$obj = $db->fetch_object($resql);
				$db->free($resql);
				$codeP = $obj->code;


				//Add new ecritur in table bank et return id
				$sql = " INSERT INTO `llx_bank`(`rowid`, `datec`, `tms`, `datev`, `dateo`, `amount`, `label`, `fk_account`, `fk_user_author`, `fk_user_rappro`, `fk_type`, `num_releve`, `num_chq`, `rappro`, `note`, `fk_bordereau`, `banque`, `emetteur`, `author`)" ;
				$sql.= " VALUES ('',NOW(),NOW(),'".$datev."','".$dateo."',".$amount.",'(SupplierInvoicePayment)',".$idAccount.",1,'','".$codeP."','','','','','','','','')";
				$resql = $db->query($sql);
				$ide = $db->last_insert_id(MAIN_DB_PREFIX . "bank");



				//Add new paiment in table paiment
				$sql = " SELECT rowid FROM llx_paiementfourn ORDER BY rowid DESC LIMIT 1";
				$resql = $db->query($sql);
				$objlastselect = $db->fetch_object($resql);
				$db->free($resql);
				$idp = $objlastselect->rowid + 1;

				$year =  date("y");
				$month =  date("m");
				$year = substr( $year, -2);
				$idpc = str_pad($idp, 4, '0', STR_PAD_LEFT);
				$idpc = 'SPAY'.$year.$month.'-'.$idpc;

				$sql = " INSERT INTO `llx_paiementfourn`(`rowid`, `ref`, `entity`, `tms`, `datec`, `datep`, `amount`, `multicurrency_amount`, `fk_user_author`, `fk_paiement`, `num_paiement`, `note`, `fk_bank`, `statut`) ";
				$sql.= " VALUES (".$idp.",'".$idpc."',1,NOW(),NOW(),NOW(),".$amount.",".$amount.",1,".$idpaiment.",'','',".$ide.",0) ";
				$resql = $db->query($sql);
				
				
				//Add new relation paiment avec facture in table paiment_facture
				foreach($montantF as $key => $val){
					$idfactura = $rowidF[$key];
					
					$sql = " INSERT INTO `llx_paiementfourn_facturefourn`(`rowid`, `fk_paiementfourn`, `fk_facturefourn`, `amount`, `multicurrency_code`, `multicurrency_tx`, `multicurrency_amount`) ";
					
					$sql.= " VALUES ('',".$idp.",".$idfactura.",".$val.",'',1,".$val.")";
					
					$resql = $db->query($sql);
				}
				
				header("Location: treatment.php");
				exit;
				
				
			}
			
		}
	}else{
		
		$error = $langs->trans("ErrorFactur");
		header("Location: treatment.php?errorinsert=".$error);
		exit;
	}
}else{
	
	foreach($_POST['rapp'] as $key => $val){
		$sql = " UPDATE `llx_bank` SET `num_releve`=".$_SESSION['arrayfilecsv']['releve'].",`rappro`=1 WHERE `rowid`= ".$_SESSION['arrayfilecsv']['reg'][$key][6] ;
		$resql = $db->query($sql);
	}
	
	$bbank = $_SESSION['arrayfilecsv']['bank'] ;
	$aacompt = $_SESSION['arrayfilecsv']['releve'] ;
	
	unset($_SESSION['arrayfilecsv']);
	
	header("Location: ".DOL_URL_ROOT."/compta/bank/releve.php?account=".$bbank."&num=".$aacompt );
	exit;
	
	/* 
	$error = $langs->trans("notFacture");
	header("Location: treatment.php?errorinsert=".$error);
	exit;
	*/
}