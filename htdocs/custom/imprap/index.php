<?php
/* Copyright (C) 2017 saasprov <saasprov@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the SAASPROV General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * SAASPROV General Public License for more details.
 *
 * You should have received a copy of the SAASPROV General Public License
 * along with this program. If not, see <http://www.saasprov.ma/>.
 */

/**
 *       \file       htdocs/compta/bank/rappro.php
 *       \ingroup    banque
 *       \brief      Page to reconciliate bank transactions
 */

 
// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
	require '../../main.inc.php'; // From "custom" directory
}
require_once DOL_DOCUMENT_ROOT.'/core/lib/bank.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/sociales/class/chargesociales.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/tva/class/tva.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/paiementfourn.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/cheque/class/remisecheque.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/bankcateg.class.php';
include_once(DOL_DOCUMENT_ROOT.'/custom/imprap/class/repartition.class.php');
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';

// Load traductions files requiredby by page
$langs->load("other");
$langs->load("banks");
$langs->load("categories");
$langs->load("bills");


$action=GETPOST('action', 'alpha');
$compteBank=GETPOST('choix', 'int');
$newFile=GETPOST('newFile', 'int');
$relevebancair=GETPOST('relevebancair', 'int');

$separateur=GETPOST('separateur');


if($action == 'deleterow'){
	$idss = $_GET['rowid'];
	unset($_SESSION['arrayfilecsv']['trait'][$idss]);
}

$form = new Form($db);

llxHeader();

$societestatic = new Societe($db);
$chargestatic = new ChargeSociales($db);
$paymentstatic = new Paiement($db);
$paymentsupplierstatic = new PaiementFourn($db);
$paymentvatstatic = new TVA($db);
$remisestatic = new RemiseCheque($db);

$acct = new Account($db);

$now = dol_now();

if(isset($_GET['errorinsert'])){
	$langs->load("errors");
	setEventMessages($_GET['errorinsert'], null, 'errors');
}


$error = 0;
$arrayfilecsv = array(
					'releve'     => '',
					'separateur' => '',
					'bank'       => '',
					'reg'        => array(),
					'trait'      => array(),
					);

if(!empty($newFile)){

	// $_SESSION['arrayfilecsv'] = '';
	if (isset($_FILES['fcsv']) AND $_FILES['fcsv']['error'] == 0)
	{	
		$dateStart1 = '';
		$dateEnd1   = '';
		if ( !empty($_POST['dated']) AND !preg_match( "/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $_POST['dated'] ) ){ 
			
			$error++;
			$langs->load("errors");
			setEventMessages($langs->trans("dateStartInvalid"), null, 'errors');
			
		}elseif(!empty($_POST['dated'])){
			
			$source = str_replace("/","-",$_POST['dated']);
			$dateStart1 = date("Y-m-d", strtotime($source));
			
		}
		
		if ( !empty($_POST['datef']) AND !preg_match( "/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $_POST['datef'] ) ){
			
			$error++;
			$langs->load("errors");
			setEventMessages($langs->trans("dateEndInvalid"), null, 'errors');
			
		}elseif(!empty($_POST['datef'])){
			
			$source = str_replace("/","-",$_POST['datef']);
			$dateEnd1 = date("Y-m-d", strtotime($source));
			
		}

		// Testons si l'extension est autorisie
		$infosfichier = pathinfo($_FILES['fcsv']['name']);
		$extension_upload = $infosfichier['extension'];
		$extensions_autorisees = array('csv');
		
		if (in_array($extension_upload, $extensions_autorisees))
		{
			// On peut valider le fichier et le stocker definitivement
			// move_uploaded_file($_FILES['fcsv']['tmp_name'], 'uploads/' . basename($_FILES['fcsv']['name']));
			// $lines = file('uploads/'.$_FILES['fcsv']['name'], FILE_IGNORE_NEW_LINES);
			
			$lines = file($_FILES['fcsv']['tmp_name'], FILE_IGNORE_NEW_LINES);
			
			if(!empty($_POST['entete'])){
				unset($lines[0]);
			}
			
			if( empty($_POST['relevebancair']) OR !is_numeric($_POST['relevebancair'])){
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("choixReleveOblig"), null, 'errors');
			}
			
			if(!empty(trim($separateur))){
				if(strlen($separateur) > 1){
					$error++;
					$langs->load("errors");
					setEventMessages($langs->trans("ErrorSeparateur"), null, 'errors');
				}
			}else{	$separateur = ';'; }
			
			if($error == 0){
				foreach ($lines as $key => $value)
				{
					$tableligne  = array();
					$cs = split(trim($separateur),$value);
					
					if ( (!empty($cs[0]) AND preg_match( "/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $cs[0] )) OR (!empty($cs[0]) AND preg_match( "/^[0-9]{4}[\/-](0[1-9]|1[0-2])[\/-](0[1-9]|[1-2][0-9]|3[0-1])$/", $cs[0] )) ){ 
						
						$originalDate1 = str_replace("/","-",$cs[0]);
						$dateOperation = date("Y-m-d", strtotime($originalDate1));
						$tableligne[] = $dateOperation;
					
					}else{ 				
						$error++;
						$langs->load("errors");
						setEventMessages($langs->trans("dateOperationInvalid"), null, 'errors');
					}
					
					if ( (!empty($cs[1]) AND preg_match( "/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $cs[1] )) OR (!empty($cs[1]) AND preg_match( "/^[0-9]{4}[\/-](0[1-9]|1[0-2])[\/-](0[1-9]|[1-2][0-9]|3[0-1])$/", $cs[1] )) ){ 
						
						$originalDate1 = str_replace("/","-",$cs[1]);
						$datevaleur = date("Y-m-d", strtotime($originalDate1));
						$tableligne[] = $datevaleur;
						
					}else{ 
						$error++;
						$langs->load("errors");
						setEventMessages($langs->trans("dateValeurInvalid"), null, 'errors');
					}
					
					$cs[2] = (float)(trim(str_replace(",",".",$cs[2])));
					$cs[3] = (float)(trim(str_replace(",",".",$cs[3])));
					
					$tableligne[] = $cs[2];
					$tableligne[] = $cs[3];
					
					if ( ($cs[2] != 0 AND $cs[3] != 0) OR ($cs[2] == 0 AND $cs[3] == 0) ){ 
						$error++;
						$langs->load("errors");
						setEventMessages($langs->trans("ErrorCreditDebit"), null, 'errors');
					}
					
					$tableligne[] = trim($cs[4]);
					
					$tableligne[] = $cs[2]+$cs[3];
					
					$arrayfilecsv['trait'][] = $tableligne;
				}
			}			
			
			if($error == 0){
				
				foreach($arrayfilecsv['trait'] as $key => $arrayfilecs){ 
				
					if(!empty($_POST['dated']) AND empty($_POST['datef'])){
						if($arrayfilecs[0] < $dateStart1){
							unset($arrayfilecsv['trait'][$key]);
						}
					}elseif(empty($_POST['dated']) AND !empty($_POST['datef'])){
						if($arrayfilecs[0] > $dateEnd1){
							unset($arrayfilecsv['trait'][$key]);
						}
					}elseif(!empty($_POST['dated']) AND !empty($_POST['datef'])){
						if($arrayfilecs[0] < $dateStart1 OR $arrayfilecs[0] > $dateEnd1){
							unset($arrayfilecsv['trait'][$key]);
						}
					}					
				}
				
				$arrayfilecsv['releve'] = $relevebancair;
				$arrayfilecsv['bank'] = $compteBank;
			
				$_SESSION['arrayfilecsv'] = $arrayfilecsv;
				
			}else{
				$_SESSION['arrayfilecsv'] = '';
			}
		}
		else
		{
			$error++;
			$langs->load("errors");
			setEventMessages($langs->trans("extentionInvalid"), null, 'errors');
		}
	}
	else
	{
		$error++;
		$langs->load("errors");
		setEventMessages($langs->trans("notFileScv"), null, 'errors');
	}
}



print '<form method="POST" action="index.php" enctype="multipart/form-data">';
print '<input type="hidden" name="action" value="uploadcsv">';
print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
print '<input type="hidden" name="newFile" value="1">';
	dol_fiche_head();
	print load_fiche_titre($langs->trans("Import fichier CSV"));
	
		print ' <table id="tb1" class="liste" width="100%">
			  <tr>
				<td>Compte bancaire</td>
				<td>
					<select name="choix">';
					   $account_bank = new repartition($db);
						foreach ($account_bank->getaccount() as $key => $value) {
							print '<option value="'.$value->rowid.'">'.$value->label.'</option>';
						}
					print '</select>
				</td>';
				
					// Date start
					print '<td class="fieldrequired">' . $langs->trans('Dated') . '</td><td>';
					$dated = $form->select_date(
								$set_time = '',
								$prefix = 'dated',
								$h = 0,
								$m = 0,
								$empty = 1,
								$form_name = "",
								$d = 1,
								$addnowlink = 0,
								$nooutput = 0,
								$disabled = 0,
								$fullday = '',
								$addplusone = '',
								$adddateof = '' 
							);
					print $dated;
					print '</td>';
				
				print'<td>Le fichier contient une ligne d\'en-téte : <input type="checkbox" name="entete" value="1"></td>
			  
			  </tr>
			  <tr>
				<td>Fichier à importer</td>
				<td><input class="flat" type="file" size="33" name="fcsv"/></td>';
					// <td>Date fin</td>
					// <td><input type="date" name="datef"/></td>
					// Date start
					print '<td class="fieldrequired">' . $langs->trans('Datef') . '</td><td>';
					$datef = $form->select_date(
								$set_time = '',
								$prefix = 'datef',
								$h = 0,
								$m = 0,
								$empty = 1,
								$form_name = "",
								$d = 1,
								$addnowlink = 0,
								$nooutput = 0,
								$disabled = 0,
								$fullday = '',
								$addplusone = '',
								$adddateof = '' 
							);
					print $datef;
					print '</td>';
					
				print'<td>relevé bancaire : <input type="number" name="relevebancair" style="width:130px;"></td>
			  </tr>
			  <tr>
				<td>Séparateur des données du fichier : </td>
				<td><input type="text" name="separateur"/></td>
				<td></td>
				<td></td>
				<td></td>
			  </tr>
		</table>';
	print'<div width=100% style="text-align:center;"><input type="submit" class="button" name="add" value="Comparer les écritures >>"></div>';
	dol_fiche_end();
print '</form>';
	

if(!empty($_SESSION['arrayfilecsv']) AND $error == 0){
	
	$sql = "SELECT b.rowid, b.dateo as do, b.datev as dv, b.amount, b.label, b.rappro, b.num_releve, b.num_chq, b.fk_type as type";
	$sql.= " FROM ".MAIN_DB_PREFIX."bank as b ";
	$sql.= " WHERE b.rappro=0 AND b.fk_account=".$_SESSION['arrayfilecsv']['bank'];
	$resql = $db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$i = 0;

			while ($i < $num)
			{
				$obj = $db->fetch_object($resql);
				
				foreach($_SESSION['arrayfilecsv']['trait'] as $key => $val){
					if($val[0] == $obj->do AND $val[1] == $obj->dv AND $val[5] == $obj->amount ){
						$_SESSION['arrayfilecsv']['trait'][$key][] = $obj->rowid;
						$_SESSION['arrayfilecsv']['reg'][] = $_SESSION['arrayfilecsv']['trait'][$key];
						unset($_SESSION['arrayfilecsv']['trait'][$key]);
					}
				}
				$i++;
			}
		}
		$db->free($resql);
	
	
	// print"<pre>";
	// print_r($_SESSION['arrayfilecsv']);
	// print"</pre>";
	
	$thirdparty_static = new Societe($db);
	
	print '<form method="POST" action="insertajax.php">';
	print '<table class="liste" width="100%">';
	print '<tr class="liste_titre">'."\n";
	print_liste_field_titre($langs->trans("DateOperationShort"),$_SERVER["PHP_SELF"],"","",$paramlist,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("DateValueShort"),$_SERVER["PHP_SELF"],"","",$paramlist,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Type"),$_SERVER["PHP_SELF"],"","",$paramlist,'align="left"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Description"),$_SERVER["PHP_SELF"],"","",$paramlist,'align="left"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Debit"),$_SERVER["PHP_SELF"],"","",$paramlist,' width="60 align="right"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Credit"),$_SERVER["PHP_SELF"],"","",$paramlist,' width="60 align="right"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("ToConciliate"),$_SERVER["PHP_SELF"],"","",$paramlist,' align="center" width="80" ',$sortfield,$sortorder);
    print "</tr>\n";
	
	foreach($_SESSION['arrayfilecsv']['reg'] as $key => $val){
		$var=!$var;
		print "<tr ".$bc[$var].">\n";
		print '<td class="nowrap" align="">'.$val[0].'</td>';
		print '<td class="nowrap" align="">'.$val[1].'</td>';
		print '<td align="">';
		
			$sql = "SELECT fk_type FROM `llx_bank` WHERE rowid=".$val[6];
			$result = $db->query($sql);
			$obj = $db->fetch_object($result);
		
			$sql = "SELECT `libelle` FROM `llx_c_paiement` WHERE code = '".$obj->fk_type."'";
			$result = $db->query($sql);
			$obj = $db->fetch_object($result);
			echo $obj->libelle;
		
		print '</td>';
		
		print '<td align="">';
		
		if($val[2] != 0){
			print'<a href='.DOL_URL_ROOT.'/compta/bank/ligne.php?rowid='.$val[6].'&account='.$_SESSION['arrayfilecsv']['bank'].'>'.$langs->trans("Règlement fournisseur").'</a><br>';
		}else{
			print'<a href='.DOL_URL_ROOT.'/compta/bank/ligne.php?rowid='.$val[6].'&account='.$_SESSION['arrayfilecsv']['bank'].'>'.$langs->trans("Règlement client").'</a><br>';
		
			$sql = "SELECT url_id FROM `llx_bank_url` WHERE fk_bank = ".$val[6]." AND `label` = '(Client)'";
			$result = $db->query($sql);
			$obj = $db->fetch_object($result);
			
			$sql = " SELECT s.rowid, s.nom as name, s.client, s.fournisseur";
			$sql.= ", s.code_client";
			$sql.= ", s.code_fournisseur";
			$sql.= ", s.logo";
			$sql.= ", s.canvas, s.tms as datem, s.status as status ";
			$sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
			$sql.= " WHERE s.rowid =".$obj->url_id;
			$result1 = $db->query($sql);
			$objp = $db->fetch_object($result1);
			
			$thirdparty_static->id=$objp->rowid;
			$thirdparty_static->name=$objp->name;
			$thirdparty_static->client=$objp->client;
			$thirdparty_static->fournisseur=$objp->fournisseur;
			$thirdparty_static->logo = $objp->logo;
			$thirdparty_static->datem=$db->jdate($objp->datem);
			$thirdparty_static->status=$objp->status;
			$thirdparty_static->code_client = $objp->code_client;
			$thirdparty_static->code_fournisseur = $objp->code_fournisseur;
			$thirdparty_static->canvas=$objp->canvas;
			
			print $thirdparty_static->getNomUrl(1);
			
			//start get facture
			
			$sql = "SELECT url_id FROM `llx_bank_url` WHERE fk_bank = ".$val[6]." AND `label` = '(paiement)'";
			$result = $db->query($sql);
			$obj = $db->fetch_object($result);
			
			
			$paymentstatic = new Paiement($db);
			
			$sql = 'SELECT `rowid`, `ref`, `datep`,  `fk_paiement`, `num_paiement`, `fk_bank` FROM `llx_paiement` WHERE `fk_bank` = '.$val[6];

			$result = $db->query($sql);

			$objp = $db->fetch_object($result);
			$paymentstatic->id=$objp->rowid;
			$paymentstatic->datepaye=$db->jdate($objp->dp);
			$paymentstatic->ref=$objp->ref;
			$paymentstatic->num_paiement=$objp->num_paiement;
			$paymentstatic->payment_code=$objp->payment_code;
			print $paymentstatic->getNomUrl(1);
	
			//end get facture
			
		}
		print '</td>';
		
		
		print '<td align="">'.$val[2].'</td>';
		print '<td align="">'.$val[3].'</td>';
		print '<td align=""><input type="checkbox" name="rapp[]" value="'.$key.'" checked></td>';
		print "</tr>\n";
	}
	
	foreach($_SESSION['arrayfilecsv']['trait'] as $key => $val){

		$filter = '';
		if(!empty($val[2])){
			$filter = "fournisseur = 1";
		}else if(!empty($val[3])){
			$filter = "client = 1";
		}
		
		$vats = $form->select_thirdparty_list(
		$selected = '',
		$htmlname = 'socid',
		$filter = $filter,
		$showempty = '',
		$showtype = 0,
		$forcecombo = 0,
		$events = array(),
		$filterkey = '',
		$outputmode = 0,
		$limit = 0,
		$morecss = 'minwidth100',
		$moreparam = '' 
		);		
	
		print "<tr ".$bc[$var].">\n";
		print "<td class='dateo'>".$val[0]."</td>";
		print "<td class='datev'>".$val[1]."</td>";
		print "<td class='desct'><span style='font-weight:bold;'>Libellé : </span>".$val[4]."</td>";
		print "<td width='25%'>
					<select name='typeG' id='typeG'>;
						<option value='facture' selected=''>Facture</option>
					</select>
					<br>".$vats."<br>
					<select id='selectpaiementtype' class='flat selectpaymenttypes' name='paiementtype'>
						<option value='6'>Carte bancaire</option>
						<option value='7'>Chèque</option>
						<option value='4' selected>Espèce</option>
						<option value='3'>Prélèvement</option>
						<option value='2'>Virement bancaire</option>
					</select>
					<div id='factures'></div>
				</td>";
		print "<td class='debit'>".$val[2]."</td>";
		print "<td class='crdit'>".$val[3]."</td>";
		print "<td>";
		print "<span style='color:red;'>".$langs->trans('notSaved')."</span><br>";
		
		print "<input type='hidden' name='idrowInSession' value='".$key."'>";
		print "<input type='submit' name='add' value='".$langs->trans('save')."'>";
		
		
		print '<a class="ajax" href="'.$_SERVER['PHP_SELF'].'?action=deleterow&amp;rowid='.$key.'">';
		print img_delete() . "</a> ";

		print "</td>";
		
		print "</tr>\n";
		break;
	}

    print "</table><br>\n";
	if(empty($_SESSION['arrayfilecsv']['trait'])){
		print '<div align="right"><input class="button" type="submit" value="'.$langs->trans("Conciliate").'"></div><br>';
    }
	print "</form>";
}

print"
<script>
    $(document).ready(function() {
	  
		$('#socid').change(function() {
			
			var debit1 = $(this).closest('tr').find('.debit').text();
			var crdit1 = $(this).closest('tr').find('.crdit').text();
			
			var idfourn = 0;
			var idclt   = 0;
			
			var testName = '';
			
			if(debit1 != 0){
				idfourn = $( 'select#socid option:checked' ).val();
				testName = 'ref';
			}else{
				idclt = $( 'select#socid option:checked' ).val();
				testName = 'facnumber';
			}
			
			$('#factures').html('');
			$.ajax({
				url: 'getajax.php?idclt='+idclt+'&idfourn='+idfourn,
				data: '',
				dataType: 'json',
				success: function(json) {
					// pour chaque noeud JSON
					$.each(json, function(index, value) {
						// on ajoute l option dans la liste
						var ttc = parseFloat(value['total_ttc']);
					$('#factures').append('<h6 style=\'margin:0;padding:0;\'>'+ value[testName] +'&nbsp;->&nbsp;'+ ttc.toFixed(2) +'&nbsp;&nbsp;<input name=\'rowidmontant[]\' type=\'hidden\' value ='+ value['rowid'] +'><input type=\'text\' name=\'montant[]\' style=\'width:60px;\'></h6>');
					});
				}
			});	
		});
	});
</script>
";



llxFooter();

$db->close();
