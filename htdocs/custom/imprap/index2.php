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

require('../../main.inc.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/bank.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/sociales/class/chargesociales.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/tva/class/tva.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/paiementfourn.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/cheque/class/remisecheque.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/bankcateg.class.php';

// start confuguration
include_once(DOL_DOCUMENT_ROOT.'/custom/imprap/class/repartition.class.php');

// Load traductions files requiredby by page
$langs->load("other");
// end confuguration

$langs->load("banks");
$langs->load("categories");
$langs->load("bills");

if (! $user->rights->banque->consolidate) accessforbidden();

$action=GETPOST('action', 'alpha');

$daletrow=GETPOST('daletrow', 'alpha');

// 
if(GETPOST('choix')){
	$id= GETPOST('choix');
}else{
	$id=GETPOST('account', 'int');
}

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$newFile = GETPOST("newFile",'alpha');
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="dateo";


/*
 * Actions
 */
  

// Conciliation

	if (isset($daletrow))
	{
		
	}
	
	
	if ($action == 'rappro' && $user->rights->banque->consolidate)
	{
		$error=0;

		// Definition, nettoyage parametres
		$num_releve=trim($_POST["num_releve"]);

		if ($num_releve)
		{
			$bankline=new AccountLine($db);

			if (isset($_POST['rowid']) && is_array($_POST['rowid']))
			{
				foreach($_POST['rowid'] as $row)
				{
					if($row > 0)
					{
						$result=$bankline->fetch($row);
						$bankline->num_releve=$num_releve; //$_POST["num_releve"];
						$result=$bankline->update_conciliation($user,$_POST["cat"]);
						if ($result < 0)
						{
							setEventMessages($bankline->error, $bankline->errors, 'errors');
							$error++;
							break;
						}
					}
				}
			}
		}
		else
		{
			$error++;
			$langs->load("errors");
			setEventMessages($langs->trans("ErrorPleaseTypeBankTransactionReportName"), null, 'errors');
		}

		if (! $error)
		{
			header('Location: '.DOL_URL_ROOT.'/compta/bank/rappro.php?account='.$id);	// To avoid to submit twice and allow back
			exit;
		}
	}

/*
 * Action suppression ecriture
 */
if ($action == 'del')
{
	$bankline = new AccountLine($db);

    if ($bankline->fetch($_GET["rowid"]) > 0) {
        $result = $bankline->delete($user);
        if ($result < 0) {
            dol_print_error($db, $bankline->error);
        }
    } else {
        setEventMessage($langs->trans('ErrorRecordNotFound'), 'errors');
    }
}

$bankcateg = new BankCateg($db);
$options = array();

foreach ($bankcateg->fetchAll() as $bankcategory) {
	$options[$bankcategory->id] = $bankcategory->label;
}

/*
 * View
 */

$form = new Form($db);

llxHeader();

$societestatic = new Societe($db);
$chargestatic = new ChargeSociales($db);
$paymentstatic = new Paiement($db);
$paymentsupplierstatic = new PaiementFourn($db);
$paymentvatstatic = new TVA($db);
$remisestatic = new RemiseCheque($db);

$acct = new Account($db);
$acct->fetch($id);

$now = dol_now();

	
//start verify of file

$error = 0;

$arrayfilecsv = array(
					'bank' => '',
					'reg' => array(),
					'trait' => array(),
					);

if(!empty($newFile)){
	if (isset($_FILES['fcsv']) AND $_FILES['fcsv']['error'] == 0)
	{	
		// Testons si le fichier n'est pas trop gros
		if ($_FILES['fcsv']['size'] <= 1000000)
		{
			if ( !empty($_POST['dated']) AND !preg_match( "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $_POST['dated'] ) ){ 
				
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("dateStartInvalid"), null, 'errors');
			
			}
			if ( !empty($_POST['datef']) AND !preg_match( "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $_POST['datef'] ) ){
				
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("dateEndInvalid"), null, 'errors');
				
			}

			// Testons si l'extension est autorisie
			$infosfichier = pathinfo($_FILES['fcsv']['name']);
			$extension_upload = $infosfichier['extension'];
			$extensions_autorisees = array('csv');
			if (in_array($extension_upload, $extensions_autorisees))
			{
				// On peut valider le fichier et le stocker definitivement
				move_uploaded_file($_FILES['fcsv']['tmp_name'], 'uploads/' . basename($_FILES['fcsv']['name']));
				
				$lines = file('uploads/'.$_FILES['fcsv']['name'], FILE_IGNORE_NEW_LINES);
				
				
				if(!empty($_POST['entete'])){
					unset($lines[0]);
				}
				
				foreach ($lines as $key => $value)
				{
					$tableligne  = array();
					$cs = split(";",$value);
					
					if ( (!empty($cs[0]) AND preg_match( "/^[0-9]{4}([-.\\/])(0[1-9]|1[0-2])([-.\\/])(0[1-9]|[1-2][0-9]|3[0-1])$/", $cs[0] )) OR (!empty($cs[0]) AND preg_match( "/^(0[1-9]|[1-2][0-9]|3[0-1])([-.\\/])(0[1-9]|1[0-2])([-.\\/])[0-9]{4}$/", $cs[0] )) ){ 
						$originalDate1 = $cs[0];
						$dateOperation = date("Y-m-d", strtotime($originalDate1));
						$tableligne[] = $dateOperation;
					}else{ 				
						$error++;
						$langs->load("errors");
						setEventMessages($langs->trans("dateOperationInvalid"), null, 'errors');
					}
					
					if ( (!empty($cs[1]) AND preg_match( "/^[0-9]{4}([-.\\/])(0[1-9]|1[0-2])([-.\\/])(0[1-9]|[1-2][0-9]|3[0-1])$/", $cs[1] )) OR (!empty($cs[1]) AND preg_match( "/^(0[1-9]|[1-2][0-9]|3[0-1])([-.\\/])(0[1-9]|1[0-2])([-.\\/])[0-9]{4}$/", $cs[1] )) ){ 
						$originalDate1 = $cs[1];
						$dateOperation = date("Y-m-d", strtotime($originalDate1));
						$tableligne[] = $dateOperation;
					}else{ $tableligne[] = ''; }
					
					$cs[2] = (float)(trim(str_replace(",",".",$cs[2])));
					if ( !empty($cs[2]) AND is_float($cs[2]) ){ 
						$tableligne[] = $cs[2];
					}else{ $tableligne[] = 0; }
					
					$cs[3] = (float)(trim(str_replace(",",".",$cs[3])));
					if ( !empty($cs[3]) AND is_float($cs[3]) ){ 
						$tableligne[] = $cs[3];
					}else{ $tableligne[] = 0 ; }
					
					$tableligne[] = trim($cs[4]);
					
					$tableligne[] = $cs[2]+$cs[3];
					
					$arrayfilecsv['trait'][] = $tableligne;
				}
				
				foreach($arrayfilecsv['trait'] as $key => $arrayfilecs){ 
					if(!empty($_POST['dated']) AND empty($_POST['datef'])){
						if($arrayfilecs[0] < $_POST['dated']){
							unset($arrayfilecsv[$key]);
						}
					}elseif(empty($_POST['dated']) AND !empty($_POST['datef'])){
						if($arrayfilecs[0] > $_POST['datef']){
							unset($arrayfilecsv[$key]);
						}
					}elseif(!empty($_POST['dated']) AND !empty($_POST['datef'])){
						if($arrayfilecs[0] < $_POST['dated'] OR $arrayfilecs[0] > $_POST['datef']){
							unset($arrayfilecsv[$key]);
						}
					}					
				}
				$arrayfilecsv['bank'] = $id;
				$_SESSION['arrayfilecsv'] = $arrayfilecsv;
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
			setEventMessages($langs->trans("bigSize"), null, 'errors');
		}
		
	}
}else{
	$arrayfilecsv = $_SESSION['arrayfilecsv'];
	print_r($arrayfilecsv);
}



//end verify of file




$sql = "SELECT b.rowid, b.dateo as do, b.datev as dv, b.amount, b.label, b.rappro, b.num_releve, b.num_chq, b.fk_type as type";
$sql.= ", b.fk_bordereau";
$sql.= ", bc.ref";
$sql.= " FROM ".MAIN_DB_PREFIX."bank as b";
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'bordereau_cheque as bc ON bc.rowid=b.fk_bordereau';
$sql.= " WHERE rappro=0 AND fk_account=".$acct->id;
$sql.= " ORDER BY $sortfield $sortorder";
$sql.= " LIMIT 1000";	// Limit to avoid page overload

/// ajax adjust value date
print '
<script type="text/javascript">
$(function() {
	$("a.ajax").each(function(){
		var current = $(this);
		current.click(function()
		{
			$.get("'.DOL_URL_ROOT.'/core/ajax/bankconciliate.php?"+current.attr("href").split("?")[1], function(data)
			{
				current.parent().prev().replaceWith(data);
			});
			return false;
		});
	});
});
</script>

';

	print '<form method="POST" action="" enctype="multipart/form-data">';
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
				</td>
				<td>Date début</td>
				<td><input type="date" name="dated"/></td>
				<td>Le fichier contient une ligne d\'en-téte : <input type="checkbox" name="entete" value="1"></td>
			  </tr>
			  <tr>
				<td>Fichier à importer</td>
				<td><input class="flat" type="file" size="33" name="fcsv"/></td>
				<td>Date fin</td>
				<td><input type="date" name="datef"/></td>
				<td style="text-align:right;"><input type="submit" class="button" name="add" value="Comparer les écritures >>"/></td>
			  </tr>
		</table>';

dol_fiche_end();

print '</form>';


if($error == 0){
$resql = $db->query($sql);
if ($resql)
{
	$var=True;
    $num = $db->num_rows($resql);

    print load_fiche_titre($langs->trans("Reconciliation").': <a href="account.php?account='.$acct->id.'">'.$acct->label.'</a>', '', 'title_bank.png');
    print '<br>';

    // Show last bank receipts
    $nbmax = 15;      // We accept to show last 15 receipts (so we can have more than one year)
    $liste = "";
    $sql = "SELECT DISTINCT num_releve FROM ".MAIN_DB_PREFIX."bank";
    $sql.= " WHERE fk_account=".$acct->id." AND num_releve IS NOT NULL";
    $sql.= $db->order("num_releve","DESC");
    $sql.= $db->plimit($nbmax+1);
    print $langs->trans("LastAccountStatements").' : ';
    $resqlr=$db->query($sql);
    if ($resqlr)
    {
        $numr = $db->num_rows($resqlr);
        $i = 0;
        $last_ok = 0;
        while (($i < $numr) && ($i < $nbmax))
        {
            $objr = $db->fetch_object($resqlr);
            if (! $last_ok) {
            $last_releve = $objr->num_releve;
                $last_ok = 1;
            }
            $i++;
            $liste = '<a href="'.DOL_URL_ROOT.'/compta/bank/releve.php?account='.$acct->id.'&amp;num='.$objr->num_releve.'">'.$objr->num_releve.'</a> &nbsp; '.$liste;
        }
        if ($numr >= $nbmax) $liste="... &nbsp; ".$liste;
        print $liste;
        if ($numr > 0) print '<br><br>';
        else print '<b>'.$langs->trans("None").'</b><br><br>';
    }
    else
    {
        dol_print_error($db);
    }


	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?account='.$acct->id.'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="rappro">';
	print '<input type="hidden" name="account" value="'.$acct->id.'">';

    print '<strong>'.$langs->trans("InputReceiptNumber").'</strong>: ';
    print '<input class="flat" name="num_releve" type="text" value="'.(GETPOST('num_releve')?GETPOST('num_releve'):'').'" size="10">';  // The only default value is value we just entered
    print '<br>';
	if ($options) {
		print $langs->trans("EventualyAddCategory").': ';
		print Form::selectarray('cat', $options, GETPOST('cat'), 1);
		print '<br>';
	}
    print '<br>'.$langs->trans("ThenCheckLinesAndConciliate").' "'.$langs->trans("Conciliate").'"<br>';

    print '<br>';

   	$paramlist = '';
	$paramlist.= "&account=".$acct->id;
	
	print '<table class="liste" width="100%">';
	print '<tr class="liste_titre">'."\n";
	print_liste_field_titre($langs->trans("DateOperationShort"),$_SERVER["PHP_SELF"],"b.dateo","",$paramlist,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("DateValueShort"),$_SERVER["PHP_SELF"],"b.datev","",$paramlist,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Type"),$_SERVER["PHP_SELF"],"b.fk_type","",$paramlist,'align="left"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Description"),$_SERVER["PHP_SELF"],"b.label","",$paramlist,'align="left"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Debit"),$_SERVER["PHP_SELF"],"b.amount","",$paramlist,' width="60 align="right"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Credit"),$_SERVER["PHP_SELF"],"b.amount","",$paramlist,' width="60 align="right"',$sortfield,$sortorder);
	print_liste_field_titre('',$_SERVER["PHP_SELF"],"","",$paramlist,' width="80 align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("ToConciliate"),$_SERVER["PHP_SELF"],"","",$paramlist,' align="center" width="80" ',$sortfield,$sortorder);
    print "</tr>\n";
	
	
    $i = 0;
	$varTestExist = false;
	
    while ($i < $num)
    {
        $objp = $db->fetch_object($resql);
		
			foreach($arrayfilecsv['trait'] as $key => $val){
				if($val[0] == $objp->do AND $val[1] == $objp->dv AND $val[5] == $objp->amount ){
					// unset($arrayfilecsv[$key]);
					echo "sfddddddddd";
					$arrayfilecsv['reg'][] = $arrayfilecsv['trait'][$key];
					unset($arrayfilecsv['trait'][$key]);
					$varTestExist = true;
					break;
				}
			}
			
			
        $i++;
		$varTestExist = false;
    }
    $db->free($resql);
	
	
foreach($arrayfilecsv['reg'] as $key => $val){
	print_r($arrayfilecsv[0]);
	print"<tr>";
	print"<td>".$val[0]."</td>";
	print"<td>".$val[1]."</td>";
	print"<td>".$val[2]."</td>";
	print"<td>".$val[3]."</td>";
	print"<td>".$val[4]."</td>";
	print"<td>".$val[5]."</td>";
	print"</tr>";
}

    /* 
	
	// unset();
	$testaffichesubmit = true;
	if(!empty($arrayfilecsv['trait'])){
	
		
		 foreach($arrayfilecsv['trait'] as $key => $val){
		
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
			print "<td class='desct'>".$val[4]."</td>";
			print "<td class='debit'>".$val[2]."</td>";
			print "<td class='crdit'>".$val[3]."</td>";
			print "<td>--</td>";
			print "<td><span style='color:red;'>".$langs->trans('notSaved')."</span><br><input type='button' id='bbenregistrer' value='".$langs->trans('save')."'><input type='hidden' name='keydaletrow' value='".$key."'><input type='submit' name='daletrow' id='bbcancel' value='".$langs->trans('cancel')."'>";
			
			print '<a class="ajax" href="'.$_SERVER['PHP_SELF'].'?action=cancelrow&amp;account='.$key.'&amp;rowid='.$objp->rowid.'">cancel</a>';
			
			print"</td>";
			
			print "</tr>\n";
			break;
		 }
		 $testaffichesubmit = false;
	}
	
	 */
	/* 
    print "</table><br>\n";
	
	if($testaffichesubmit){
		print '<div align="right"><input class="button" type="submit" value="'.$langs->trans("Conciliate").'"></div><br>';
	}
	
    print "</form>\n";
 */
}

/* 
print"<pre>";
print_r($arrayfilecsv);
print"</pre>";
 */
 
print"
<script>
  $(document).ready(function() {
	  
	  $('#bbcancel').click(function() {
		  alert('bien');
		  
			var dateo = $(this).closest('tr').find('.dateo').text();
			var datev = $(this).closest('tr').find('.datev').text();
			var desct = $(this).closest('tr').find('.desct').text();
			var debit = $(this).closest('tr').find('.debit').text();
			var crdit = $(this).closest('tr').find('.crdit').text();
			
			alert(desct);
			
	  });
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
					$('#factures').append('<h6 style=\'margin:0;padding:0;\'>'+ value[testName] +'&nbsp;->&nbsp;'+ value['total_ttc'] +'<input class=\'input1\' type=\'hidden\' value ='+ value['rowid'] +'><input type=\'text\' class=\'input2\' style=\'width:60px;\'></h6>');
				});
			}
		});
	 });
	 
	 $('#bbenregistrer').click(function() {
		if ($('#factures > h6').length < 1) {
			alert('".$langs->trans('SelectBilling')."');
		}else{
			
			var typeG = $('#typeG').val();
			
			var idclient = 0;
			var idfourn  = 0;
			
			var idpaiment = $('#selectpaiementtype').val();
			
			var dateo = $(this).closest('tr').find('.dateo').text();
			var datev = $(this).closest('tr').find('.datev').text();
			var desct = $(this).closest('tr').find('.desct').text();
			var debit = $(this).closest('tr').find('.debit').text();
			var crdit = $(this).closest('tr').find('.crdit').text();
			
			if(debit != 0){
				idfourn = $( 'select#socid option:checked' ).val();
			}else{
				idclient = $( 'select#socid option:checked' ).val();
			}
			var testm = true;
			
			var montantF = new Array();
			var rowidF = new Array();
			
			$('#factures > h6').each(function(index,value) {
				
				rowidF.push($(value).find('input:eq(0)').val());
				montantF.push($(value).find('input:eq(1)').val());
				
				if(isNaN($(value).find('input:eq(1)').val())){
					$(value).find('input:eq(1)').css('border-color','red'); 
					testm = false;
				}
			});
		
			if(testm == true){
				$.ajax({
					url: 'insertajax.php?rowidF= '+ rowidF +'&montantF= '+ montantF +'&idfourn= '+ idfourn +'&idclient= '+ idclient +'&typeG= '+ typeG +'&idpaiment= '+ idpaiment +'&dateo= '+ dateo +'&datev= '+ datev +'&desct= '+ desct +'&debit= '+ debit +'&crdit= '+ crdit +'&idAccount= '+".$id.",
					data: '',
					dataType: 'json',
					success: function(json) {
						alert(json);
						location.reload();
					}
				});
			}
			
		}
			
	 });
	 
  });
</script>
";
}
llxFooter();

$db->close();
