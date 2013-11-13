<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013      Alexandre Spangaro   <alexandre.spangaro@gmail.com> 
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
 * 		\file       htdocs/accountingex/admin/chartofaccounts.php
 * 		\ingroup    Accounting Expert
 * 		\brief      List chart of accounts and accounting account
 */
 
 // Dolibarr environment
 
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class

dol_include_once ( "/ventilation/class/accountingaccount.class.php");
dol_include_once ( "/ventilation/class/html.formventilation.class.php");



// langs
$langs->load("compta");
$langs->load("ventilation@ventilation");

// Security check
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();

//filter
$pcg_version = GETPOST ( "pcg_version", 'alpha' );
$sortfield = GETPOST ( "sortfield", 'alpha' );
$sortorder = GETPOST ( "sortorder", 'alpha' );
if (! $pcg_version)
	$pcg_version = "1";
if (! $sortfield)
	$sortfield = "aa.rowid";
if (! $sortorder)
	$sortorder = "ASC";
	
//import account



	
	
	
	
	
	
	
	

llxHeader('',$langs->trans("Chartofaccounts"));

print_barre_liste($langs->trans("Chartofaccounts"),$page,"chartofaccounts.php","",$sortfield,$sortorder,'',$num);

/*
* List accounting account
*/

/*
print '<form action="'.$_SERVER["PHP_SELF"].'" method="GET">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'" />';

$var=True;

print '<input type="hidden" name="action" value="pcg_version">';
print $langs->trans("Selectchartofaccounts").'<br/>';
print '<select class="flat" name="chartofaccounts" id="chartofaccounts">';

$sql = "SELECT rowid, pcg_version, fk_pays, label, active";
$sql.= " FROM ".MAIN_DB_PREFIX."accounting_system";
$sql.= " WHERE active = 1";
$sql.= " AND fk_pays = ".$mysoc->country_id;
$var=True;
$resql=$db->query($sql);
if ($resql)
{
    $num = $db->num_rows($resql);
    $i = 0;
    while ($i < $num)
    {
        //$var=!$var;
        $row = $db->fetch_row($resql);

        print '<option value="'.$row[0].'"';
        print $conf->global->CHARTOFACCOUNTS == $row[0] ? ' selected="selected"':'';
        print '>'.$row[1].' - '.$row[3].'</option>';

        $i++;
    }
}
print '</select>';
print '&nbsp;&nbsp;<input type="submit" class="button" value="'.$langs->trans("Validate").'">';
print '&nbsp;&nbsp;<input type="submit" class="button" value="'.$langs->trans("Edit").'">';
print '</form>';
print '<br/>';

*/

$sql2 = "SELECT aa.rowid, aa.fk_pcg_version, aa.pcg_type, aa.pcg_subtype, aa.account_number, aa.account_parent , aa.label, aa.active ";
$sql2 .= " FROM " . MAIN_DB_PREFIX . "accountingaccount as aa";
//$sql2 .= " WHERE aa.fk_pcg_version =".$pcg_version;
$sql2 .= " ORDER BY $sortfield $sortorder"; // . $db->plimit ( $conf->liste_limit + 1, $offset );

$result = $db->query($sql2);
if ($result)
{
	$num = $db->num_rows($result);
	$i = 0;
	
		$var = true;
	
	$param = '';
	
	$obj = $db->fetch_object ( $result );
	
	
  print '<form method="GET" action="' . $_SERVER ["PHP_SELF"] . '">';
	
  print '<a class="butAction" href="fiche.php?action=create">'.$langs->trans("Addanaccount").'</a>';
  print '<a class="butAction" href="importaccounts.php">' . $langs->trans ( "ImportAccount" ) . '</a>';
  print '<br/><br/>';
	
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print_liste_field_titre ( $langs->trans ( "AccountNumber" ), "chartofaccounts.php", "aa.account_number", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre ( $langs->trans ( "Label" ), "chartofaccounts.php", "aa.label" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Accountparent" ), "chartofaccounts.php", "aa.account_parent" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Pcgtype" ), "chartofaccounts.php", "aa.pcg_type" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Pcgsubtype" ), "chartofaccounts.php", "aa.pcg_subtype", "", $param, "", $sortfield, $sortorder  );
	print_liste_field_titre ( $langs->trans ( "Active" ), "chartofaccounts.php", "aa.active" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( "&nbsp;" );
	print '</tr>';

  print '<tr class="liste_titre">';
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_account" value="' . GETPOST ( "search_account" ) . '"></td>';
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_label" value="' . GETPOST ( "search_label" ) . '"></td>';
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_accountparent" value="' . GETPOST ( "search_accountparent" ) . '"></td>';
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_pcgtype" value="' . GETPOST ( "search_pcgtype" ) . '"></td>';
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_pcgsubtype" value="' . GETPOST ( "search_pcgsubtype" ) . '"></td>';
	print '<td class="liste_titre">&nbsp;</td>';
	print '<td class="liste_titre" align="right">';
	print '<input type="image" class="liste_titre" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" name="button_search" value="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '" title="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '">';
	print '</td>';
	print '</tr>';



$var = True;
		
		while ( $i < min ( $num, 1000 ) ) {
			$obj2 = $db->fetch_object ( $resql2 );
			$var = ! $var;
			
			print "<tr $bc[$var]>";

      print '<td><a href="./fiche.php?id='. $obj2->rowid . '">' . $obj2->account_number . '</td>';
			print '<td>' . $obj2->label . '</td>';
			print '<td>' . $obj2->account_parent . '</td>';
			print '<td>' . $obj2->pcg_type . '</td>';
			print '<td>' . $obj2->pcg_subtype . '</td>';
			print '<td>' . $obj2->active . '</td>';
      print '<td><a href="./fiche.php?action=update&id=' . $obj2->rowid . '">';
			print img_edit ();
			print '</a>&nbsp;</td>' . "\n";
			print "</tr>";
		$i ++;
	}
		
	print "</table>";
		print '</form>';
		}
	else {
	print $db->error ();
}

	
	llxFooter ( '' );


$db->close ();
?>
