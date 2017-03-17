<?php
/* Copyright (C) 2017 saasprov <saasprov@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
// 
if(GETPOST('choix')){
	$id= GETPOST('choix');
}else{
	$id=GETPOST('account', 'int');
}

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="dateo";


/*
 * Actions
 */
  

// Conciliation
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
$arrayfilecsv = array();
$fileUploadTest = false;
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
			
			$fileUploadTest = true;
			
			unset($lines[0]);
			
			foreach ($lines as $key => $value)
			{
				$tableligne  = array();
				$cs = split(";",$value);
				
				if ( (!empty($cs[0]) AND preg_match( "/^[0-9]{4}([-.\\/])(0[1-9]|1[0-2])([-.\\/])(0[1-9]|[1-2][0-9]|3[0-1])$/", $cs[0] )) OR (!empty($cs[0]) AND preg_match( "/^(0[1-9]|[1-2][0-9]|3[0-1])([-.\\/])(0[1-9]|1[0-2])([-.\\/])[0-9]{4}$/", $cs[0] )) ){ 
					$originalDate1 = $cs[0];
					$dateOperation = date("Y-m-d", strtotime($originalDate1));
					$tableligne[] = $dateOperation;
				}else{ die("La date operation in csv est invalid");  }
				
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
				
				$arrayfilecsv[] = $tableligne;
			}
			
			foreach($arrayfilecsv as $key => $arrayfilecs){
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
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
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
				<td></td>
				<td></td>
			  </tr>
			  <tr>
				<td>Fichier à importer</td>
				<td><input class="flat" type="file" size="33" name="fcsv"/></td>
				<td>Date fin</td>
				<td><input type="date" name="datef"/></td>
				<td colspan=2 style="text-align:right;"><input type="submit" class="button" name="add" value="Comparer les écritures >>"/></td>
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
		
		if($fileUploadTest){
			foreach($arrayfilecsv as $key => $val){
				if($val[0] == $objp->do AND $val[1] == $objp->dv AND $val[5] == $objp->amount ){
					unset($arrayfilecsv[$key]);
					$varTestExist = true;
					break;
				}
			}
		}else{
			$varTestExist = true;
		}
		
		if($varTestExist)
		{
			$var=!$var;
			print "<tr ".$bc[$var].">\n";
			
			print '<td align="center" class="nowrap">'.dol_print_date($db->jdate($objp->do),"day").'</td>';

			// Date value
			if (! $objp->rappro && ($user->rights->banque->modifier || $user->rights->banque->consolidate))
			{
				print '<td align="center" class="nowrap">'."\n";
				print '<span id="datevalue_'.$objp->rowid.'">'.dol_print_date($db->jdate($objp->dv),"day")."</span>";
				print '&nbsp;';
				print '<span>';
				print '<a class="ajax" href="'.$_SERVER['PHP_SELF'].'?action=dvprev&amp;account='.$acct->id.'&amp;rowid='.$objp->rowid.'">';
				print img_edit_remove() . "</a> ";
				print '<a class="ajax" href="'.$_SERVER['PHP_SELF'].'?action=dvnext&amp;account='.$acct->id.'&amp;rowid='.$objp->rowid.'">';
				print img_edit_add() ."</a>";
				print '</span>';
				print '</td>';
			}
			else
			{
				print '<td align="center">';
				print dol_print_date($db->jdate($objp->dv),"day");
				print '</td>';
			}

			// Type + Number
			$label=($langs->trans("PaymentType".$objp->type)!="PaymentType".$objp->type)?$langs->trans("PaymentType".$objp->type):$objp->type;  // $objp->type is a code
			if ($label == 'SOLD') $label = '';
			$link = '';
			if ($objp->fk_bordereau>0) {
				$remisestatic->id = $objp->fk_bordereau;
				$remisestatic->ref = $objp->number;
				$link = ' '.$remisestatic->getNomUrl(1);
			}
			print '<td class="nowrap">'.$label.($objp->num_chq?' '.$objp->num_chq:'').$link.'</td>';

			// Description
			print '<td valign="center"><a href="'.DOL_URL_ROOT.'/compta/bank/ligne.php?rowid='.$objp->rowid.'&amp;account='.$acct->id.'">';
			$reg=array();
			preg_match('/\((.+)\)/i',$objp->label,$reg);	// Si texte entoure de parentheses on tente recherche de traduction
			if ($reg[1] && $langs->trans($reg[1])!=$reg[1]) print $langs->trans($reg[1]);
			else print $objp->label;
			print '</a>';

			/*
			 * Ajout les liens (societe, company...)
			 */
			$newline = 1;
			$links = $acct->get_url($objp->rowid);
			
			
			foreach($links as $key=>$val)
			{
			
				if ($newline == 0) print ' - ';
				else if ($newline == 1) print '<br>';
				if ($links[$key]['type']=='payment') {
					$paymentstatic->id=$links[$key]['url_id'];
					print ' '.$paymentstatic->getNomUrl(2);
					$newline = 0;
				}
				elseif ($links[$key]['type']=='payment_supplier') {
					$paymentsupplierstatic->id=$links[$key]['url_id'];
					$paymentsupplierstatic->ref=$links[$key]['label'];
					print ' '.$paymentsupplierstatic->getNomUrl(1);
					$newline = 0;
				}
				elseif ($links[$key]['type']=='company') {
					$societestatic->id=$links[$key]['url_id'];
					$societestatic->name=$links[$key]['label'];
					print $societestatic->getNomUrl(1,'',24);
					$newline = 0;
				}
				else if ($links[$key]['type']=='sc') {
					$chargestatic->id=$links[$key]['url_id'];
					$chargestatic->ref=$links[$key]['url_id'];
					$chargestatic->lib=$langs->trans("SocialContribution");
					print ' '.$chargestatic->getNomUrl(1);
				}
				else if ($links[$key]['type']=='payment_sc')
				{
					// We don't show anything because there is 1 payment for 1 social contribution and we already show link to social contribution
					/*print '<a href="'.DOL_URL_ROOT.'/compta/payment_sc/card.php?id='.$links[$key]['url_id'].'">';
					print img_object($langs->trans('ShowPayment'),'payment').' ';
					print $langs->trans("SocialContributionPayment");
					print '</a>';*/
					$newline = 2;
				}
				else if ($links[$key]['type'] == 'payment_vat')
				{
					$paymentvatstatic->id = $links[$key]['url_id'];
					$paymentvatstatic->ref = $links[$key]['url_id'];
					$paymentvatstatic->ref = $langs->trans("VATPayment");
					print ' '.$paymentvatstatic->getNomUrl(1);
				}
				else if ($links[$key]['type'] == 'banktransfert') {
					print '<a href="'.DOL_URL_ROOT.'/compta/bank/ligne.php?rowid='.$links[$key]['url_id'].'">';
					print img_object($langs->trans('ShowTransaction'),'payment').' ';
					print $langs->trans("TransactionOnTheOtherAccount");
					print '</a>';
				}
				else if ($links[$key]['type'] == 'member') {
					print '<a href="'.DOL_URL_ROOT.'/adherents/card.php?rowid='.$links[$key]['url_id'].'">';
					print img_object($langs->trans('ShowMember'),'user').' ';
					print $links[$key]['label'];
					print '</a>';
				}
				else {
					//print ' - ';
					print '<a href="'.$links[$key]['url'].$links[$key]['url_id'].'">';
					if (preg_match('/^\((.*)\)$/i',$links[$key]['label'],$reg))
					{
						// Label generique car entre parentheses. On l'affiche en le traduisant
						if ($reg[1] == 'paiement') $reg[1]='Payment';
						print $langs->trans($reg[1]);
					}
					else
					{
						print $links[$key]['label'];
					}
					print '</a>';
					$newline=0;
				}
			}
			print '</td>';

			if ($objp->amount < 0)
			{
				print "<td align=\"right\" nowrap>".price($objp->amount * -1)."</td><td>&nbsp;</td>\n";
			}
			else
			{
				print "<td>&nbsp;</td><td align=\"right\" nowrap>".price($objp->amount)."</td>\n";
			}

			if ($objp->rappro)
			{
				// If line already reconciliated, we show receipt
				print "<td align=\"center\" nowrap=\"nowrap\"><a href=\"releve.php?num=$objp->num_releve&amp;account=$acct->id\">$objp->num_releve</a></td>";
			}
			else
			{
				// If not already reconciliated
				if ($user->rights->banque->modifier)
				{
					print '<td align="center" width="30" class="nowrap">';

					print '<a href="'.DOL_URL_ROOT.'/compta/bank/ligne.php?rowid='.$objp->rowid.'&amp;account='.$acct->id.'&amp;orig_account='.$acct->id.'">';
					print img_edit();
					print '</a>&nbsp; ';

					$now=dol_now();
					if ($db->jdate($objp->do) <= $now) {
						print '<a href="'.DOL_URL_ROOT.'/compta/bank/rappro.php?action=del&amp;rowid='.$objp->rowid.'&amp;account='.$acct->id.'">';
						print img_delete();
						print '</a>';
					}
					else {
						print "&nbsp;";	// We prevents the deletion because reconciliation can not be achieved until the date has elapsed and that writing appears well on the account.
					}
					print "</td>";
				}
				else
				{
					print "<td align=\"center\">&nbsp;</td>";
				}
			}

			// Show checkbox for conciliation
			if ($db->jdate($objp->do) <= $now)
			{
				print '<td align="center" class="nowrap">';
				print '<input class="flat" name="rowid['.$objp->rowid.']" type="checkbox" value="'.$objp->rowid.'" size="1"'.(! empty($_POST['rowid'][$objp->rowid])?' checked':'').'>';
				print "</td>";
			}
			else
			{
				print '<td align="left">';
				print $langs->trans("FutureTransaction");
				print '</td>';
			}

			print "</tr>\n";
		
		}
        $i++;
		$varTestExist = false;
    }
    $db->free($resql);
	
	
	$testaffichesubmit = true;
	if(!empty($arrayfilecsv)){	
	
		$vats = $form->select_thirdparty_list(
		$selected = '',
		$htmlname = 'socid',
		$filter = '',
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
		
		 foreach($arrayfilecsv as $key => $val){
			print "<tr ".$bc[$var].">\n";
			print "<td class='dateo'>".$val[0]."</td>";
			print "<td class='datev'>".$val[1]."</td>";
			print "<td>
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
			print "<td><span style='color:red;'>".$langs->trans("NotSaved")."</span><br><input type='button' id='bbenregistrer' value='".$langs->trans("Save")."'></td>";
			
			print "</tr>\n";
			break;
		 }
		 $testaffichesubmit = false;
	}
	
	
	
    print "</table><br>\n";
	
	if($testaffichesubmit){
		print '<div align="right"><input class="button" type="submit" value="'.$langs->trans("Conciliate").'"></div><br>';
	}
	
    print "</form>\n";

}
else
{
  //dol_print_error($db);
}
 
print"
<script>
  $(document).ready(function() {
	  
	 $('#socid').change(function() {
		val = $( 'select#socid option:checked' ).val();
		$('#factures').html('');
		$.ajax({
			url: 'getajax.php?id='+val,
			data: '',
			dataType: 'json',
			success: function(json) {
				// pour chaque noeud JSON
				$.each(json, function(index, value) {
					// on ajoute l option dans la liste
					$('#factures').append('<h6 style=\'margin:0;padding:0;\'><input type=\'text\' name=\'valuef[]\' readonly value ='+ value['facnumber'] +'-'+ value['total_ttc'] +'><input type=\'radio\' id=\'radio_1\' name=\'type\' value='+value['rowid']+' /></h6>');
				});
			}
		});
	 });
	 
	 $('#bbenregistrer').click(function() {
		if (! $('input[name=\'type\']:checked').val()) {
			alert('".$langs->trans('SelectBilling')."');
		}else{
			var typeG = $('#typeG').val();
			var idpaiment = $('#selectpaiementtype').val();
			var idclient = $( 'select#socid option:checked' ).val();
			var idpaiment = $('#selectpaiementtype').val();
		    var idfactura = $('input[name=\'type\']:checked').val();
			
			var dateo = $(this).closest('tr').find('.dateo').text();
			var datev = $(this).closest('tr').find('.datev').text();
			var desct = $(this).closest('tr').find('.desct').text();
			var debit = $(this).closest('tr').find('.debit').text();
			var crdit = $(this).closest('tr').find('.crdit').text();
			
			$.ajax({
				url: 'insertajax.php?idclient= '+ idclient +'&typeG= '+ typeG +'&idpaiment= '+ idpaiment +'&idfactura= '+ idfactura +'&dateo= '+ dateo +'&datev= '+ datev +'&desct= '+ desct +'&debit= '+ debit +'&crdit= '+ crdit +'&idAccount= '+".$id.",
				data: '',
				dataType: 'json',
				success: function(json) {
					alert(json);
					location.reload();
				}
			});
			
		}
			
	 });
	 
  });
</script>
";
}
llxFooter();

$db->close();
