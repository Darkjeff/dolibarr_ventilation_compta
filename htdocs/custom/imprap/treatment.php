<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <2017> SaaSprov <saasprov@gmail.com>
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
 * \defgroup    imprap    ImpRap module
 * \brief       ImpRap module descriptor.
 *
 * Put detailed description here.
 */

/**
 * \file        core/modules/modImpRap.class.php
 * \ingroup     imprap
 * \brief       Example module description and activation file.
 *
 * Put detailed description here.
 */
 
if (false === (@include '../main.inc.php')) {  // From htdocs directory
	require '../../main.inc.php'; // From "custom" directory
}

require_once(DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php');
include_once(DOL_DOCUMENT_ROOT.'/custom/imprap/lib/bankrapprofile.lib.php');


if(isset($_GET['errorinsert'])){
	$langs->load("errors");
	setEventMessages($_GET['errorinsert'], null, 'errors');
}

if(isset($_GET['bieninsert'])){
	setEventMessages($_GET['bieninsert'], null);
}


$action = GETPOST('action', 'alpha');
$dated = GETPOST('dated');
$datef = GETPOST('datef');
$numReleve = GETPOST('numReleve');
$accountid = GETPOST('accountid');
$ligne = GETPOST('ligne');


$idpaiment = GETPOST('idpaiment');
$idsoc = GETPOST('idsoc');


$error = 0 ;
$vartest = true;
if($action == "rappro"){
	if(isset($_POST['checkbank'])){
		
		foreach($_POST['checkbank'] as $key => $val){
			$sql = "UPDATE `llx_bank` SET `num_releve`='".$numReleve."' ,`rappro`= 1 WHERE rowid = ".$val;
			$resql = $db->query($sql);
		}
		$action = "comparer";
		setEventMessages($langs->trans("bienRappro"), null);
	}
}

if($action == "comparer"){
		
		if (empty($numReleve)){
			$error++;
			$langs->load("errors");
			setEventMessages($langs->trans("numReleve"), null, 'errors');		
		}
		
		if (!empty($dated)){
			if(preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $dated ) OR preg_match("/^[0-9]{4}[\/-](0[1-9]|1[0-2])[\/-](0[1-9]|[1-2][0-9]|3[0-1])$/", $dated )){
				
				$originalDate = str_replace("/","-",$dated);
				$dated = date("Y-m-d", strtotime($originalDate));
			}else{
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("dated"), null, 'errors');
			}			
		}
		
		if (!empty($datef)){
			if(preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $datef ) OR preg_match("/^[0-9]{4}[\/-](0[1-9]|1[0-2])[\/-](0[1-9]|[1-2][0-9]|3[0-1])$/", $datef )){
				$originalDate = str_replace("/","-",$datef);
				$datef = date("Y-m-d", strtotime($originalDate));
			}else{
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("datef"), null, 'errors');
			}			
		}
		
	if($error == 0){
		if($dated != '' and $datef == ''){
			
			$datef = date('Y-m-d',strtotime($dated) + (24*3600*30));
			
			$sql1 = "SELECT `rowid`, `date_d_operation`, `date_de_valeur`, `debut`, `credit`, `libelle`, `solde`";
			$sql1.= " FROM `llx_bank_rapprofile` WHERE date_d_operation BETWEEN '".$dated."' AND '".$datef ."' AND fk_account = ".$accountid;
			$sql2 = "SELECT `rowid`, `datev`, `dateo`, `amount`, `label`, `fk_account`, `fk_type`,`rappro`, `banque`";
			$sql2.= " FROM " . MAIN_DB_PREFIX . "bank  WHERE dateo BETWEEN '".$dated."' AND '".$datef ."' AND fk_account = ".$accountid." AND rappro = 0 ";	
			
		}elseif($dated == '' and $datef != ''){
			
			$dated = date('Y-m-d',strtotime($datef) - (24*3600*30));
			
			$sql1 = "SELECT `rowid`, `date_d_operation`, `date_de_valeur`, `debut`, `credit`, `libelle`, `solde`";
			$sql1.= " FROM `llx_bank_rapprofile` WHERE date_d_operation BETWEEN '".$dated."' AND '".$datef ."' AND fk_account = ".$accountid;

			$sql2 = "SELECT `rowid`, `datev`, `dateo`, `amount`, `label`, `fk_account`, `fk_type`,`rappro`, `banque`";
			$sql2.= " FROM " . MAIN_DB_PREFIX . "bank  WHERE dateo BETWEEN '".$dated."' AND '".$datef ."' AND fk_account = ".$accountid." AND rappro = 0 ";	
			
		}elseif($dated != '' and $datef != ''){
			$sql1 = "SELECT `rowid`, `date_d_operation`, `date_de_valeur`, `debut`, `credit`, `libelle`, `solde`";
			$sql1.= " FROM `llx_bank_rapprofile` WHERE date_d_operation BETWEEN '".$dated."' AND '".$datef ."' AND fk_account = ".$accountid;

			$sql2 = "SELECT `rowid`, `datev`, `dateo`, `amount`, `label`, `fk_account`, `fk_type`,`rappro`, `banque`";
			$sql2.= " FROM " . MAIN_DB_PREFIX . "bank  WHERE dateo BETWEEN '".$dated."' AND '".$datef ."' AND fk_account = ".$accountid." AND rappro = 0 ";		
		}else{
			
			$vartest = false;
		}
	}else{
		$vartest = false;
	}
	
}else{
	$vartest = false;
}

$now=dol_now();

$form = new Form($db);

llxHeader();

// Onglets
$head = bank_rappro_file();
dol_fiche_head($head, 'treatment', $langs->trans("rappro"), 0 ,'account');
print "<style>.minwidth100 { width: 100px; }</style>";
print "<form method='POST' action='treatment.php?action=comparer'>";
print "<table width='100%'>";
	print"<tr>";
	
		print '<td>'. $langs->trans('CompteBank') .'</td>';
		print '<td>';
			$form->select_comptes(	 	
				$selected = $accountid,
				$htmlname = 'accountid',
				$statut = 0,
				$filtre = '',
				$useempty = 0,
				$moreattrib = '' 
			);
		print"</td>";
		print '<td>'. $langs->trans('numReleve') .'</td>';
		print '<td><input type="text" name="numReleve" value="'.$numReleve.'"></td>';
		print '<td class="fieldrequired">' . $langs->trans('Dated') . '</td><td>';
		
		$datedebut = $form->select_date(
					$set_time = $dated,
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
		print $datedebut;
		
		print '</td>';
	  
		print '<td class="fieldrequired">' . $langs->trans('Datef') . '</td><td>';
		$datefin = $form->select_date(
					$set_time = $datef,
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
		print $datefin;
		print '</td>';
		print '<td>';
			print '<input type="submit" class="button" value="'.$langs->trans('Comparer').'">';
		print '</td>';
		
	print '</tr>';
print "</table>";
print "</form>";

dol_fiche_end();

if($vartest){
	
print '<div class="fichecenter"><div class="fichethirdleft">';

	$resql = $db->query($sql1);
	if ($resql)
	{
		$var=false;
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("DateOperation").'</td>';
		print '<td>'.$langs->trans("DateValeur").'</td>';
		print '<td>'.$langs->trans("Libelle").'</td>';
		print '<td>'.$langs->trans("Debut").'</td>';
		print '<td>'.$langs->trans("Credit").'</td>';
		print '<td></td>';
		print '</tr>';
		
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);
				$style = '' ;
				
				$resqlv = $db->query($sql2);
				if ($resqlv)
				{
					$num2 = $db->num_rows($resqlv);
					$j = 0;
					
					if ($num2)
					{
						while ($j < $num2)
						{
							$objpv = $db->fetch_object($resqlv);
							
							if($objp->date_d_operation == $objpv->dateo AND $objp->date_de_valeur == $objpv->datev){
								if( ($objp->debut == $objpv->amount AND $objpv->label == '(SupplierInvoicePayment)') OR ($objp->credit == $objpv->amount AND $objpv->label == '(CustomerInvoicePayment)') ) {
									$style = 'style="color:green;"' ;
								}
							}							
							$j++;
						}
					}
				}
				
				
				$display = 'none';
				if(!empty($ligne) AND $ligne == $objp->rowid){ 
					$display = 'inline-block';
				}
				
				print '<tr '.$bc[$var].' '.$style.'>';
				print '<td class="nowrap">'. $objp->date_d_operation .'</td>';
				print '<td class="nowrap">'. $objp->date_de_valeur .'</td>';
				
				print '<td class="nowrap">'. substr($objp->libelle,0,30) .'...<br>';
				print '<form action="insertajax.php" method="POST" >';
				
				print '<input type="hidden" name="dated" value="'.$dated.'" >';
				print '<input type="hidden" name="datef" value="'.$datef.'" >';
				print '<input type="hidden" name="numReleve" value="'.$numReleve.'" >';
				print '<input type="hidden" name="accountid" value="'.$accountid.'" >';
				
				print '<input type="hidden" name="row" value='. $objp->rowid .'>';
				print '<div class="info" style="display:'.$display.';">';
				
					$filter = '';
					if(!empty($objp->debut)){
						$filter = "fournisseur = 1";
					}else if(!empty($objp->credit)){
						$filter = "client = 1";
					}
					
				print $form->select_thirdparty_list(
						$selected = $idsoc,
						$htmlname = 'idsoc'.$objp->rowid,
						$filter = $filter,
						$showempty = '',
						$showtype = 0,
						$forcecombo = 0,
						$events = array(),
						$filterkey = '',
						$outputmode = 0,
						$limit = 0,
						$morecss = 'minwidth100 idsoc'
						// $moreparam = 'onload="myfunction()"' 
					);
					
				print $form->select_types_paiements	(	 	
						$selected = $idpaiment,
						$htmlname = 'paiementtype'.$objp->rowid,
						$filtertype = '',
						$format = 0,
						$empty = 0,
						$noadmininfo = 0,
						$maxlength = 5,
						$active = 1,
						$morecss = ''
					); 

					
				print "<input type='submit' value='save' ><br><div class='infoform'></div>";
	
				print '</div>';
				print '</form>';
				
				print '</td>';
				
				print '<td class="nowrap debut">'. $objp->debut .'</td>';
				print '<td class="nowrap crdit">'. $objp->credit .'</td>';
				
				if($style == ''){
					print '<td><input type="button" name="checkbankp" value="&#8595;" class="btntreat" /></td>';
				}else{
					print '<td></td>';
				}
				
				print '</tr>';
				
				
				$i++;
				$var=!$var;
			}

			$db->free($resql);
		}
		else
		{
			print '<tr '.$bc[$var].'><td colspan="5" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}
		print "</table><br>";
	}


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


	$resql = $db->query($sql2);
	if ($resql)
	{
		$var=false;
		$num = $db->num_rows($resql);
		$i = 0;

		print '<form method="POST" action="treatment.php?action=rappro">';

		print '<input type="hidden" name="dated" value="'.$dated.'" >';
		print '<input type="hidden" name="datef" value="'.$datef.'" >';
		print '<input type="hidden" name="numReleve" value="'.$numReleve.'" >';
		print '<input type="hidden" name="accountid" value="'.$accountid.'" >';
		
		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td></td>';
		print '<td>'.$langs->trans("DateOperation").'</td>';
		print '<td>'.$langs->trans("DateValeur").'</td>';
		print '<td>'.$langs->trans("Libelle").'</td>';
		print '<td>'.$langs->trans("Amount").'</td>';
		print '</tr>';
		
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);
				
				$checked = 0 ;
				$resqlv = $db->query($sql1);
				if ($resqlv)
				{
					$num2 = $db->num_rows($resqlv);
					$j = 0;
					
					if ($num2)
					{
						while ($j < $num2)
						{
							$objpv = $db->fetch_object($resqlv);
							
							if($objpv->date_d_operation == $objp->dateo AND $objpv->date_de_valeur == $objp->datev AND ($objpv->debut == $objp->amount OR $objpv->credit == $objp->amount) ){
								$checked = "checked";
							}
							$j++;
						}
					}
				}
				
				print '<tr '.$bc[$var].'>';
				print '<td><input type="checkbox" name="checkbank[]" value="'. $objp->rowid .'"'.$checked.' /> </td>';
				print '<td class="nowrap">'. $objp->dateo .'</td>';
				print '<td class="nowrap">'. $objp->datev .'</td>';
				print '<td class="nowrap">'. substr($objp->label,0,30) .'...</td>';
				print '<td class="nowrap">'.$objp->amount.'</td>';	
				print '</tr>';
			
				$i++;
				$var=!$var;
			}
			
			
			$db->free($resql);
		}
		else
		{
			print '<tr '.$bc[$var].'><td colspan="5" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}
		print "</table><input type='hidden' name='numReleve' value='".$numReleve."'><input type='submit' value='submit'/></form><br>";
	}

print '</div></div></div>';


}

print"
<script>
    $(document).ready(function() {
		
		
		
		$('.btntreat').click(function() {
			
			var info = $(this).closest('tr').find('.info');
			var idsoc = $(this).closest('tr').find('.idsoc');
			
			if (info.css('display') == 'none')
			{
				$('.info').css('display', 'none');
				info.css('display', 'inline-block');
				getfactures(idsoc);
			}else{
				$('.info').css('display', 'none');
			}
			
		});
		
		
		$('.idsoc').change(function(){ getfactures(this) });
		
		$(window).load(function(){ 
			ligne = '".$ligne."';
			if(ligne != ''){
				getfactures('#idsoc'+ligne);
			}
		});
		
		function getfactures(select) {
			
			var info = $(select).closest('tr').find('.infoform');
			
			var debut = $(select).closest('tr').find('.debut').text();
			var crdit = $(select).closest('tr').find('.crdit').text();
			
			var idfourn = 0;
			var idclt   = 0;
			
			var testName = '';
			
			if(debut != 0){
				idfourn = $(select).closest('tr').find('select').val();
				testName = 'ref';
			}else{
				idclt = $(select).closest('tr').find('select').val();
				testName = 'facnumber';
			}
			
			info.html('');
			$.ajax({
				url: 'getajax.php?idclt='+idclt+'&idfourn='+idfourn,
				data: '',
				dataType: 'json',
				success: function(json) {
					// pour chaque noeud JSON
					$.each(json, function(index, value) {
						// on ajoute l option dans la liste
						var ttc = parseFloat(value['total_ttc']);
						info.append('<h6 style=\'margin:0;padding:0;\'>'+ value[testName] +'&nbsp;->&nbsp;'+ ttc.toFixed(2) +'&nbsp;&nbsp;<input name=\'rowidmontant[]\' type=\'hidden\' value ='+ value['rowid'] +'><input type=\'text\' name=\'montant[]\' style=\'width:60px;\'></h6>');
					});
				}
			});
			
		}
	
	});

</script>
";

llxFooter();

$db->close();
