<?PHP
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *      \file       htdocs/accountingaccount/fiche.php
 *      \ingroup    Accounting Expert
 *      \brief      Page fiche accounting
 */
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
require_once DOL_DOCUMENT_ROOT . '/ventilation/class/accountingaccount.class.php';
require_once DOL_DOCUMENT_ROOT . '/ventilation/class/html.formventilation.class.php';


// langs
$langs->load("bills");
$langs->load("ventilation@ventilation");


$mesg = '';
$action = GETPOST ( 'action' );
$id = GETPOST ( "id" );
$html = new Form ( $db );

// Securite acces client
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();

//action
if (GETPOST ( "action" ) == 'add') {
	
	$accounting = new AccountingAccount ( $db );
	
	$accounting->fk_pcg_version = GETPOST ( "fk_pcg_version" );
	$accounting->pcg_type = GETPOST ( "pcg_type" );
	$accounting->pcg_subtype = GETPOST ( "pcg_subtype" );
	$accounting->account_number = GETPOST ( "account_number" );
	$accounting->account_parent = GETPOST ( "account_parent" );
	$accounting->label = GETPOST ( "label" );
	$accounting->active = GETPOST ( "active" );
	
	
	$e_accounting = $accounting;
	
	$res = $accounting->create (  );
	if ($res == 0) {
	} else {
		if ($res == - 3) {
			$_error = 1;
			$action = "create";
		}
		if ($res == - 4) {
			$_error = 2;
			$action = "create";
		}
	}
	Header ( "Location: " . DOL_DOCUMENT_ROOT . "/ventilation/accountingaccount/liste.php" );
} elseif (GETPOST ( "action" ) == 'maj') {
	
	$error = 0;
	
	
	
	
	
		$accounting = new AccountingAccount ( $db, GETPOST ( 'id' ) );
		
		
	$accounting->fk_pcg_version = GETPOST ( "fk_pcg_version" );
	$accounting->pcg_type = GETPOST ( "pcg_type" );
	$accounting->pcg_subtype = GETPOST ( "pcg_subtype" );
	$accounting->account_number = GETPOST ( "account_number" );
	$accounting->account_parent = GETPOST ( "account_parent" );
	$accounting->label = GETPOST ( "label" );
	$accounting->active = GETPOST ( "active" );
		
		$e_accounting = $accounting;
		
		$res = $charge->update ();
			header ( "Location: " . DOL_DOCUMENT_ROOT . "/ventilation/accountingaccount/fiche.php?id=" . $accounting->id );
		
		if ($res >= 0) {
			setEventMessage ( $langs->trans ( "SocialContributionAdded" ), 'mesgs' );
		} else
			dol_print_error ( $db );
		
	
	}

/*
 * View
 *
 */
if ($action == 'create') {
	
	llxheader ( '', $langs->trans ( "addaccounting" ), '' );
	
	$nbligne = 0;
	
	print '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">' . $langs->trans ( "AccountNumber" ) . '</td>';
	print '<td><input name="AccountNumber" size="30" value="' . $accounting->AccountNumber . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "Label" ) . '</td>';
	print '<td><input name="Label" size="30" value="' . $accounting->Label . '"</td></tr>';
  print '<tr><td width="20%">' . $langs->trans ( "Accountparent" ) . '</td>';
	print '<td><input name="AccountParent" size="30" value="' . $accounting->AccountParent . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "Pcgtype" ) . '</td>';
	print '<td><input name="pcgType" size="30" value="' . $accounting->pcgType . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "Pcgsubtype" ) . '</td>';
	print '<td><input name="pcgSubType" size="30" value="' . $accounting->pcgSubType . '"</td></tr>';	
	print '<tr><td width="20%">' . $langs->trans ( "Active" ) . '</td>';
	print '<td><input name="Active" size="30" value="' . $accounting->Active . '"</td></tr>';	
	print '</table>';
	
  print '<br/>';
  print '<center>';
  print '<input type="submit" value="'.$langs->trans("Save").'" name="bouton" class="button">';
  print '&nbsp; &nbsp; ';
  print '<input type="button" value="'.$langs->trans("Cancel").'" class="button" onclick="history.go(-1)">';
  print '</center>';
        
	print '</form>';
}

elseif ($action == 'update') {
	
	llxheader ( '', $langs->trans ( "changeaccounting" ), '' );
	
	$nbligne = 0;
	
	$accounting = new Accountingaccount ( $db , GETPOST ( 'id' ));

	
	
	print '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">' . $langs->trans ( "AccountNumber" ) . '</td>';
	print '<td><input name="AccountNumber" size="30" value="' . $accounting->AccountNumber . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "Label" ) . '</td>';
	print '<td><input name="Label" size="30" value="' . $accounting->Label . '"</td></tr>';
  print '<tr><td width="20%">' . $langs->trans ( "AccountParent" ) . '</td>';
	print '<td><input name="AccountParent" size="30" value="' . $accounting->AccountParent . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "pcgType" ) . '</td>';
	print '<td><input name="pcgType" size="30" value="' . $accounting->pcgType . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "pcgSubType" ) . '</td>';
	print '<td><input name="pcgSubType" size="30" value="' . $accounting->pcgSubType . '"</td></tr>';	
	print '<tr><td width="20%">' . $langs->trans ( "Active" ) . '</td>';
	print '<td><input name="Active" size="30" value="' . $accounting->Active . '"</td></tr>';	
	
	print '</table>';
	
  print '<br/>';
  print '<center>';
  print '<input type="submit" value="'.$langs->trans("Save").'" name="bouton" class="button">';
  print '&nbsp; &nbsp; ';
  print '<input type="button" value="'.$langs->trans("Cancel").'" class="button" onclick="history.go(-1)">';
  print '</center>';
	print '</form>';
}

llxFooter ( '' );
$db->close ();
?>