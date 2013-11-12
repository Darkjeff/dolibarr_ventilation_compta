<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013      Alexandre Spangaro   <alexandre.spangaro@fidurex.fr> 
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 */


/**
 * 		\file       htdocs/compta/ventilation/liste.php
 * 		\ingroup    compta
 * 		\brief      Page IMPORT COMPTE COMPTABLE
 */
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
dol_include_once ( "/ventilation/class/accountingaccount.class" );

$langs->load("compta");
$langs->load("bills");
$langs->load("main");
$langs->load("ventilation@ventilation");

if (!$user->rights->ventilation->access) accessforbidden();

// Securite acces client
if ($user->societe_id > 0) accessforbidden();


llxHeader('',$langs->trans("ImportAccount"));

if($_POST["action"] == 'ventil')
{
	print '<div><font color="red">'.$langs->trans("Processing").'...</font></div>';
	if($_POST['codeventil'] && $_POST["mesCasesCochees"])
	{
		print '<div><font color="red">'.count($_POST["mesCasesCochees"]).' '.$langs->trans("SelectedLines").'</font></div>';
		$mesLignesCochees=$_POST['mesCasesCochees'];
		$mesCodesVentilChoisis = $_POST['codeventil'];
		$cpt = 0;
		foreach($mesLignesCochees as $maLigneCochee) 
		{
		
		$AccountingAccount = new AccountingAccount ($db);
		
			//print '<div><font color="red">id selectionnee : '.$monChoix."</font></div>";
			$maLigneCourante = split("_", $maLigneCochee);
			$monAccount = $maLigneCourante[0];
			$monLabel = $maLigneCourante[1];
			$monParentAccount = $maLigneCourante[2];
			//$monCompte = $mesCodesVentilChoisis[$monNumLigne];
  
			$AccountingAccount->fk_pcg_version = $conf->global->CHARTOFACCOUNTS;
			$AccountingAccount->account_number = $monAccount;			
			$AccountingAccount->label = $monLabel;
			$AccountingAccount->account_parent = $monParentAccount;
			
			
			$result = $AccountingAccount->create ( $user );
			if ($result > 0) {
				setEventMessage ( $langs->trans ( "AccountingAccountAdd" ), 'mesgs' );
			} else {
				dol_print_error ( $db );
			}
			$cpt++; 
  
		}
	}
	else
	{
		print '<div><font color="red">'.$langs->trans("AnyLineImport").'</font></div>';
	}
	print '<div><font color="red">'.$langs->trans("EndProcessing").'</font></div>';
}

/* 
 * Liste des comptes
*/

$sqlCompte = "SELECT rowid, account_number, label";
$sqlCompte .= " FROM ".MAIN_DB_PREFIX."accountingaccount";
$sqlCompte .= " ORDER BY account_number ASC";

$resultCompte = $db->query($sqlCompte);
$cgs = array();
$cgn = array();
if ($resultCompte)
{
	$numCompte = $db->num_rows($resultCompte);
	$iCompte = 0; 
  
	while ($iCompte < $numCompte)
	{
		$rowCompte = $db->fetch_row($resultCompte);
		$cgs[$rowCompte[0]] = $rowCompte[1] . ' ' . $rowCompte[2];
		$cgn[$rowCompte[1]] = $rowCompte[0];
		$iCompte++;
	}
}

/*
* list accounting account from product 
*
*/
$page = $_GET["page"];
if ($page < 0) $page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page ;

$sql = "(SELECT p.rowid as product_id, p.accountancy_code_sell as accounting ";
$sql.= " FROM  ".MAIN_DB_PREFIX."product as p ";
$sql.= " WHERE p.accountancy_code_sell >=0";
$sql .= " GROUP BY accounting ";
$sql .= ")";
$sql .= "UNION ALL(SELECT p.rowid as product_id, p.accountancy_code_buy as accounting ";
$sql.= " FROM  ".MAIN_DB_PREFIX."product as p ";
$sql.= " WHERE p.accountancy_code_buy >=0";
$sql .= " GROUP BY accounting ";
$sql .= ") ";
$sql.= " ORDER BY accounting DESC ".$db->plimit($limit+1,$offset);

$result = $db->query($sql);
if ($result)
{
	$num_lignes = $db->num_rows($result);
	$i = 0;
	print_barre_liste($langs->trans("ImportAccount"),$page,"importaccounts.php","",$sortfield,$sortorder,'',$num_lignes);


	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td>'.$langs->trans("accountingaccount").'</td>';
	print '<td>'.$langs->trans("label").'</td>';
	print '<td>'.$langs->trans("parentaccount").'</td>';
  print '<td align="center">'.$langs->trans("Import").'</td>';
	print '</tr>';


  	$form = new Form($db);

	print '<form action="liste2.php" method="post">'."\n";
	print '<input type="hidden" name="action" value="ventil">';

	$var=True;
	while ($i < min($num_lignes, $limit))
	{
		$objp = $db->fetch_object($result);
		$var=!$var;
		print "<tr $bc[$var]>";

		
		
		
		print '<td align="left">';
		print $objp->accounting;
		print '</td>';	
		
		print '<td align="left">';
		print '<input name="intitule" size="70" value="">';
		print '</td>';	

		//Colonne choix du compte
		print '<td align="left">';
		print $form->selectarray("codeventil[]",$cgs, $cgn[$objp->accounting]);
		print '</td>';
		//Colonne choix ligne a ventiler
		
		$checked = ('intitule' == 'O')?' checked=checked':'';
		
		print '<td align="center">';
		print '<input type="checkbox" name="mesCasesCochees[]"'.$checked .'/>';
		print '</td>';

		print '</tr>';
		$i++;
	}

	print '<tr><td colspan="8">&nbsp;</td></tr><tr><td colspan="8" align="center"><input type="submit" class="butAction" value="'.$langs->trans("Import").'"></td></tr>';

	print '</table>';
	print '</form>';
}
else
{
	print $db->error();
}
$db->close();

llxFooter();
?>
