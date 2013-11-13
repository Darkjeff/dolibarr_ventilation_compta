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
 * 		\file       htdocs/accountingex/admin/account.php
 * 		\ingroup    Accounting Expert
 * 		\brief      List accounting account
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
	
if ($action == 'delete') {
	$formconfirm = $html->formconfirm ( $_SERVER ["PHP_SELF"] . '?id=' . $id, $langs->trans ( 'DeleteAccount' ), $langs->trans ( 'ConfirmDeleteAccount' ), 'confirm_delete', '', 0, 1 );
	print $formconfirm;
}

llxHeader('',$langs->trans("Accounts"));

print_barre_liste($langs->trans("Accounts"),$page,"account.php","",$sortfield,$sortorder,'',$num);

// $pcgver = $conf->global->ACCOUNTING_PCG_VERSION; pour clause where en fonction du plan comptable

$sql2 = "SELECT aa.rowid, aa.fk_pcg_version, aa.pcg_type, aa.pcg_subtype, aa.account_number, aa.account_parent , aa.label, aa.active ";
$sql2 .= " FROM " . MAIN_DB_PREFIX . "accountingaccount as aa";
$sql2 .= " WHERE aa.active = 1";
if (strlen(trim($_GET["search_account"])))
{
	$sql2 .= " AND aa.account_number like '%".$_GET["search_account"]."%'";
}
if (strlen(trim($_GET["search_label"])))
{
	$sql2 .= " AND aa.label like '%".$_GET["search_label"]."%'";
}
if (strlen(trim($_GET["search_accountparent"])))
{
	$sql2 .= " AND aa.account_parent like '%".$_GET["search_accountparent"]."%'";
}
if (strlen(trim($_GET["search_pcgtype"])))
{
	$sql2 .= " AND aa.pcg_type like '%".$_GET["search_pcgtype"]."%'";
}
if (strlen(trim($_GET["search_pcgsubtype"])))
{
	$sql2 .= " AND aa.pcg_subtype like '%".$_GET["search_pcgsubtype"]."%'";
}
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
	print_liste_field_titre ( $langs->trans ( "AccountNumber" ), "account.php", "aa.account_number", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre ( $langs->trans ( "Label" ), "account.php", "aa.label" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Accountparent" ), "account.php", "aa.account_parent" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Pcgtype" ), "account.php", "aa.pcg_type" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Pcgsubtype" ), "account.php", "aa.pcg_subtype", "", $param, "", $sortfield, $sortorder  );
	print_liste_field_titre ( $langs->trans ( "Active" ), "account.php", "aa.active" , "", $param, "", $sortfield, $sortorder );
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
			
				print '<td>';
						if ($user->rights->compta->ventilation->parametrer) {
							print '<a href="./fiche.php?action=update&id=' . $obj2->rowid  . '">';
							print img_edit ();
							print '</a>&nbsp;';
							print '<a href="./fiche.php?action=delete&id=' . $obj2->rowid  . '">';
							print img_delete ();
							print '</a>';
						}
						print '</td>' . "\n";
			
			
 
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
