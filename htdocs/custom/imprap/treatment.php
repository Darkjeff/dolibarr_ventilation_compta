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

require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once '/lib/bankrapprofile.lib.php';


$action = GETPOST('action', 'alpha');
$dated = GETPOST('dated');
$datef = GETPOST('datef');
$numReleve = GETPOST('numReleve');
$accountid = GETPOST('accountid');

$error = 0 ;
$vartest = true;

if($action == "comparer"){
			
		if (!empty($dated)){
			if(preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $dated )){
				$originalDate = str_replace("/","-",$dated);
				$dated = date("Y-m-d", strtotime($originalDate));
			}else{
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("dated"), null, 'errors');
			}			
		}
		
		if (!empty($datef)){
			if(preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $datef )){
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
			$sql1 = "SELECT `rowid`, `date_d_operation`, `date_de_valeur`, `debut`, `credit`, `libelle`, `solde`";
			$sql1.= " FROM `llx_bank_rapprofile` WHERE date_d_operation >= '".$dated."' AND fk_account = ".$accountid;

			$sql2 = "SELECT `rowid`, `datev`, `dateo`, `amount`, `label`, `fk_account`, `fk_type`,`rappro`, `banque`";
			$sql2.= " FROM " . MAIN_DB_PREFIX . "bank  WHERE dateo >= '".$dated ."' AND fk_account = ".$accountid;	
			
		}elseif($dated == '' and $datef != ''){
			$sql1 = "SELECT `rowid`, `date_d_operation`, `date_de_valeur`, `debut`, `credit`, `libelle`, `solde`";
			$sql1.= " FROM `llx_bank_rapprofile` WHERE date_d_operation <= '".$datef ."' AND fk_account = ".$accountid;

			$sql2 = "SELECT `rowid`, `datev`, `dateo`, `amount`, `label`, `fk_account`, `fk_type`,`rappro`, `banque`";
			$sql2.= " FROM " . MAIN_DB_PREFIX . "bank  WHERE dateo <= '".$datef ."' AND fk_account = ".$accountid;	
			
		}elseif($dated != '' and $datef != ''){
			$sql1 = "SELECT `rowid`, `date_d_operation`, `date_de_valeur`, `debut`, `credit`, `libelle`, `solde`";
			$sql1.= " FROM `llx_bank_rapprofile` WHERE date_d_operation BETWEEN '".$dated."' AND '".$datef ."' AND fk_account = ".$accountid;

			$sql2 = "SELECT `rowid`, `datev`, `dateo`, `amount`, `label`, `fk_account`, `fk_type`,`rappro`, `banque`";
			$sql2.= " FROM " . MAIN_DB_PREFIX . "bank  WHERE dateo BETWEEN '".$dated."' AND '".$datef ."' AND fk_account = ".$accountid;		
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
		$dated = $form->select_date(
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
		print $dated;
		print '</td>';
	  
		print '<td class="fieldrequired">' . $langs->trans('Datef') . '</td><td>';
		$datef = $form->select_date(
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
		print $datef;
		print '</td>';
		print '<td>';
			print '<input type="submit" class="button" value="'.$langs->trans('Comparer').'">';
		print '</td>';
		
	print '</tr>';
print "</table>";
print "</form>";

dol_fiche_end();

if($vartest){
	
print "<form method='POST' action='treatment.php?action='>";
print '<div class="fichecenter"><div class="fichethirdleft">';

	$resql = $db->query($sql1);
	if ($resql)
	{
		$var=false;
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td></td>';
		print '<td>'.$langs->trans("DateOperation").'</td>';
		print '<td>'.$langs->trans("DateValeur").'</td>';
		print '<td>'.$langs->trans("Libelle").'</td>';
		print '<td>'.$langs->trans("Debut").'</td>';
		print '<td>'.$langs->trans("Credit").'</td>';
		print '</tr>';
		
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);
				$test = true ;
				
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
							
							$credit = 0;
							$debut = 0;
							if($objpv->amount > 0){ 
								$credit =  $objpv->amount; 
							}else{
								$debut = $objpv->amount;
							}
							
							if($objp->date_d_operation == $objpv->dateo AND $objp->date_de_valeur == $objpv->datev AND $objp->libelle == $objpv->label AND $objp->debut == $debut AND $objp->credit == $credit ){
								
								$test = false ;
								print '<tr '.$bc[$var].' style="color:green;">';
								print '<td><input type="checkbox" name="checkbankp" value="'. $objp->rowid .'"/> </td>';
								print '<td class="nowrap">'. $objp->date_d_operation .'</td>';
								print '<td class="nowrap">'. $objp->date_de_valeur .'</td>';
								print '<td class="nowrap">'. substr($objp->libelle,0,30) .'...</td>';
								print '<td class="nowrap">'. $objp->debut .'</td>';
								print '<td class="nowrap">'. $objp->credit .'</td>';
								print '</tr>';
							}
							$j++;
						}
					}
				}
				
				if($test){
					print '<tr '.$bc[$var].'>';
					print '<td><input type="checkbox" name="checkbankp" value="'. $objp->rowid .'"/> </td>';
					print '<td class="nowrap">'. $objp->date_d_operation .'</td>';
					print '<td class="nowrap">'. $objp->date_de_valeur .'</td>';
					print '<td class="nowrap">'. substr($objp->libelle,0,30) .'...</td>';
					print '<td class="nowrap">'. $objp->debut .'</td>';
					print '<td class="nowrap">'. $objp->credit .'</td>';
					print '</tr>';
				}
				
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

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td></td>';
		print '<td>'.$langs->trans("DateOperation").'</td>';
		print '<td>'.$langs->trans("DateValeur").'</td>';
		print '<td>'.$langs->trans("Libelle").'</td>';
		print '<td>'.$langs->trans("Debut").'</td>';
		print '<td>'.$langs->trans("Credit").'</td>';
		print '</tr>';
		
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);
				
				print '<tr '.$bc[$var].'>';
				print '<td><input type="checkbox" name="checkbank" value="'. $objp->rowid .'"/> </td>';
				print '<td class="nowrap">'. $objp->dateo .'</td>';
				print '<td class="nowrap">'. $objp->datev .'</td>';
				print '<td class="nowrap">'. substr($objp->label,0,30) .'...</td>';
				print '<td class="nowrap">';
					if($objp->amount > 0){ 
						echo $objp->amount; 
					} 
				print '</td>';	
				print '<td class="nowrap">';
					if($objp->amount < 0){ 
						echo $objp->amount; 
					} 
				print '</td>';	
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

print '</div></div></div>';

// print "<div><input type='submit'></div>";
print "</form>";

}

llxFooter();

$db->close();
