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
 * 		\file       htdocs/accountingaccount/liste.php
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
require_once DOL_DOCUMENT_ROOT . '/accountingex/class/accountingaccount.class.php';
require_once DOL_DOCUMENT_ROOT . '/accountingex/class/html.formventilation.class.php';


// langs
$langs->load("compta");
$langs->load("accountingex@accountingex");

// Securite accès client
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();

//filter
$sortfield = GETPOST ( "sortfield", 'alpha' );
$sortorder = GETPOST ( "sortorder", 'alpha' );
$page = GETPOST ( "page" );
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
if (! $sortfield)
	$sortfield = "aa.rowid";
if (! $sortorder)
	$sortorder = "ASC";
$offset = $limit * $page;

llxHeader('',$langs->trans("Chartofaccounts"));

/*
* List accounting account
*/

$sql = "SELECT aa.rowid, aa.fk_pcg_version, aa.pcg_type, aa.pcg_subtype, aa.account_number, aa.account_parent , aa.label, aa.active ";
$sql .= " FROM " . MAIN_DB_PREFIX . "accountingaccount as aa";
$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit ( $conf->liste_limit + 1, $offset );


$result = $db->query($sql);
if ($result)
{
	$num = $db->num_rows($result);
	$i = 0;
	
		$var = true;
	
	$param = '';
	
	$obj = $db->fetch_object ( $result );
	
	print_barre_liste($langs->trans("Chartofaccounts"),$page,"liste.php","",$sortfield,$sortorder,'',$num);

  print '<form method="GET" action="' . $_SERVER ["PHP_SELF"] . '">';
	
	print '<table class="noborder" width="100%">';
	print '<tr></tr>';
	print '<a class="butAction" href="fiche.php?action=create">Ajouter un nouveau compte</a>';
	print '<tr class="liste_titre">';
	print_liste_field_titre ( $langs->trans ( "AccountNumber" ), "liste.php", "aa.account_number", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre ( $langs->trans ( "Label" ), "liste.php", "aa.label" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Accountparent" ), "liste.php", "aa.account_parent" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Pcgtype" ), "liste.php", "aa.pcg_type" , "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Pcgsubtype" ), "liste.php", "aa.pcg_subtype", "", $param, "", $sortfield, $sortorder  );
	print_liste_field_titre ( $langs->trans ( "Active" ), "liste.php", "aa.active" , "", $param, "", $sortfield, $sortorder );
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
		
		while ( $i < min ( $num, $conf->liste_limit ) ) {
			$obj = $db->fetch_object ( $resql );
			$var = ! $var;
			
			print "<tr $bc[$var]>";

      print '<td>' . $obj->account_number . '</td>';
			print '<td>' . $obj->label . '</td>';
			print '<td>' . $obj->account_parent . '</td>';
			print '<td>' . $obj->pcg_type . '</td>';
			print '<td>' . $obj->pcg_subtype . '</td>';
			print '<td>' . $obj->active . '</td>';
      print '<td><a href="./fiche.php?action=update&id=' . $obj->rowid . '">';
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
