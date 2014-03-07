<?PHP
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * $Id: liste.php,v 1.12 2011/07/31 22:23:31 eldy Exp $
 */

/**
        \file       accountingex/admin/thirdpartyaccount.php
        \ingroup    compta
        \brief      Onglet de gestion de parametrages des ventilations
*/

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/report.lib.php';
require_once (DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");

$langs->load("companies");
$langs->load("compta");
$langs->load("main");
$langs->load("accountingex@accountingex");

// Security check
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();


llxHeader ( '', $langs->trans ( "Accounts" ) );



$form=new Form($db);

// Cas des autres parametres COMPTA_*
$list=array('COMPTA_PRODUCT_BUY_ACCOUNT','COMPTA_PRODUCT_SOLD_ACCOUNT','COMPTA_SERVICE_BUY_ACCOUNT','COMPTA_SERVICE_SOLD_ACCOUNT');

$num=count($list);
if ($num)
{
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td colspan="3">'.$langs->trans('OtherOptions').'</td>';
	print "</tr>\n";
}

foreach ($list as $key)
{
	$var=!$var;

	print '<form action="compta.php" method="POST">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="consttype" value="string">';
	print '<input type="hidden" name="constname" value="'.$key.'">';
	
	print '<tr '.$bc[$var].' class="value">';

	// Param
	$libelle = $langs->trans($key); 
	print '<td>'.$libelle;
	//print ' ('.$key.')';
	print "</td>\n";

	// Value
	print '<td>';
	print '<input type="text" size="20" name="constvalue" value="'.$conf->global->$key.'">';
	print '</td><td>';
	print '<input type="submit" class="button" value="'.$langs->trans('Modify').'" name="button"> &nbsp; ';
	print "</td></tr>\n";
	print '</form>';
	
	$i++;
}

if ($num)
{
	print "</table>\n";
}

 
print '<input type="button" class="button" style="float: right;" value="Renseigner les comptes comptables produits manquant" onclick="launch_export();" />';

print '
	<script type="text/javascript">
		function launch_export() {
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("export_csv");
			$("div.fiche div.tabBar form input[type=\"submit\"]").click();
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("");
		}
</script>';

$sql = "SELECT p.rowid, p.ref , p.label, p.description , p.accountancy_code_sell as codesell, p.accountancy_code_buy, p.tms, p.fk_product_type ";
$sql.= " FROM ".MAIN_DB_PREFIX."product as p";
$sqm.= "WHERE codesell =  'NULL'";

    
$resql = $db->query($sql);
if ($resql)
{
  $num = $db->num_rows($resql);
  $i = 0;





/*
* view
*/


print '<br><br>';

print '<table class="noborder" width="100%">';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr><tr><td colspan=2>';
print '<table class="noborder" width="100%">';
print '<td align="left">'.$langs->trans("Ref").'</td>';
print '<td align="left">'.$langs->trans("Label").'</td>';
print '<td align="left">'.$langs->trans("Description").'</td>';
print '<td align="left">'.$langs->trans("Accountancy_code_buy").'</td>';
print '<td align="left">'.$langs->trans("Accountancy_code_buy_sugest").'</td>';
print '<td align="left">'.$langs->trans("Accountancy_code_sell").'</td>';
print '<td align="left">'.$langs->trans("Accountancy_code_sell_sugest").'</td>';



  $var=True;

  while ($i < min($num,250))
    {
      $obj = $db->fetch_object($resql);
      $var=!$var;
      
      	$compta_prodsell = $obj->accountancy_code_sell;
		if (empty($compta_prodsell))
		{
			if($obj->product_type == 0) $compta_prodsell = (! empty($conf->global->COMPTA_PRODUCT_SOLD_ACCOUNT)?$conf->global->COMPTA_PRODUCT_SOLD_ACCOUNT:$langs->trans("CodeNotDef"));
			else $compta_prodsell = (! empty($conf->global->COMPTA_SERVICE_SOLD_ACCOUNT)?$conf->global->COMPTA_SERVICE_SOLD_ACCOUNT:$langs->trans("CodeNotDef"));
		}
		
		$compta_prodbuy = $obj->accountancy_code_buy;
		if (empty($compta_prodbuy))
		{
			if($obj->product_type == 0) $compta_prodbuy = (! empty($conf->global->COMPTA_PRODUCT_BUY_ACCOUNT)?$conf->global->COMPTA_PRODUCT_BUY_ACCOUNT:$langs->trans("CodeNotDef"));
			else $compta_prodbuy = (! empty($conf->global->COMPTA_SERVICE_BUY_ACCOUNT)?$conf->global->COMPTA_SERVICE_BUY_ACCOUNT:$langs->trans("CodeNotDef"));
		}

 print "<tr $bc[$var]>";
 print '<td align="left">'.$obj->ref.'</td>'."\n";
 print '<td align="left">'.$obj->label.'</td>';
 print '<td align="left">'.$obj->description.'</td>';
  print '<td align="left">'.$obj->accountancy_code_buy.'</td>';
    print '<td align="left">'.$compta_prodbuy.'</td>';
 print '<td align="left">'.$obj->accountancy_code_sell.'</td>';
  print '<td align="left">'.$compta_prodsell.'</td>';

   


      print "</tr>\n";
      $i++;
    }
  print "</table>";
  $db->free($resql);
}
else
{
  dol_print_error($db);
}

$db->close();

llxFooter();
?>
