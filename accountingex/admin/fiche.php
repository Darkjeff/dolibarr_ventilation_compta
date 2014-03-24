<?PHP
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro   <alexandre.spangaro@gmail.com> 
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file      accountingex/admin/fiche.php
 * \ingroup   Accounting Expert
 * \brief     Page fiche de compte
 */

$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");
	
// Class
dol_include_once("/accountingex/core/lib/account.lib.php");
dol_include_once("/accountingex/class/accountingaccount.class.php");
dol_include_once("/accountingex/class/html.formventilation.class.php");

// Langs
$langs->load ("bills");
$langs->load ("accountingex@accountingex");

$mesg = '';
$action = GETPOST ( 'action' );
$id = GETPOST ( 'id', 'int' );
$rowid = GETPOST ( 'rowid', 'int' );

// Security check
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();
$accounting = new AccountingAccount ( $db );

// action
if ($action == 'add') {

$sql = 'SELECT pcg_version FROM ' . MAIN_DB_PREFIX . 'accounting_system WHERE rowid=' . $conf->global->CHARTOFACCOUNTS;
		$result = $db->query ( $sql );
		$obj = $db->fetch_object ( $result );
		$cpt = 0;
		
	$accounting->fk_pcg_version = $obj->pcg_version;
	$accounting->pcg_type = GETPOST ( 'pcgType' );
	$accounting->pcg_subtype = GETPOST ( 'pcgSubType' );
	$accounting->account_number = GETPOST ( 'AccountNumber', 'int' );
	$accounting->account_parent = GETPOST ( 'AccountParent', 'int' );
	$accounting->label = GETPOST ( 'Label', 'alpha' );
	$accounting->active = 1;
	
	$e_accounting = $accounting;
	
	$res = $accounting->create ( $user );
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
	Header ( "Location: account.php" );
} // Update record
else if ($action == 'edit') {
	if (! GETPOST ( 'cancel', 'alpha' )) {
		$result = $accounting->fetch ( $id );
		
		$sql = 'SELECT pcg_version FROM ' . MAIN_DB_PREFIX . 'accounting_system WHERE rowid=' . $conf->global->CHARTOFACCOUNTS;
		$result2 = $db->query ( $sql );
		$obj = $db->fetch_object ( $result2 );
		$cpt = 0;
		
		
		$accounting->fk_pcg_version = $obj->pcg_version;
		$accounting->pcg_type = GETPOST ( 'pcgType' );
		$accounting->pcg_subtype = GETPOST ( 'pcgSubType' );
		$accounting->account_number = GETPOST ( 'AccountNumber', 'int' );
		$accounting->account_parent = GETPOST ( 'AccountParent', 'int' );
		$accounting->label = GETPOST ( 'Label', 'alpha' );
		
		$result = $accounting->update ( $user );
		
		if ($result > 0) {
			header ( "Location: " . $_SERVER ["PHP_SELF"] . "?id=" . $id );
			exit ();
		} else {
			$mesg = $object->error;
		}
	} else {
		header ( "Location: " . $_SERVER ["PHP_SELF"] . "?id=" . $id );
		exit ();
	}
} else if ($action == 'disable') {
	
	$result = $accounting->fetch ( $id );
	if (!empty($accounting->id)) {
		$result = $accounting->account_desactivate ( $user );
	}
	
	$action = 'update';
	if ($result < 0) {
		setEventMessage ( $accounting->error, 'errors' );
	}
} else if ($action == 'enable') {
	
	$result = $accounting->fetch ( $id );

	if (!empty($accounting->id)) {
		$result = $accounting->account_activate ( $user );
	}
	$action = 'update';
	if ($result < 0) {
		setEventMessage ( $accounting->error, 'errors' );
	}
}else if ($action == 'delete') {
	
	$result = $accounting->fetch ( $id );

	if (!empty($accounting->id)) {
		$result = $accounting->delete ( $user );
		
		if ($result>0) {
			Header ( "Location: account.php" );
		}
		
	}
	
	if ($result < 0) {
		setEventMessage ( $accounting->error, 'errors' );
	}
}

/*
 * View
 *
 */
llxheader('',$langs->trans('AccountAccounting'));

$form = new Form ( $db );
$htmlacc = new FormVentilation ( $db );

$linkback='<a href="'.DOL_URL_ROOT.'/accountingex/admin/account.php">'.$langs->trans("BackToChartofaccounts").'</a>';

if ($action == 'create') {
	
	print_fiche_titre($langs->trans('NewAccount'));
	
	print '<form name="add" action="' . $_SERVER ["PHP_SELF"] . '" method="POST">' . "\n";
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="25%">' . $langs->trans ( "AccountNumber" ) . '</td>';
	print '<td><input name="AccountNumber" size="30" value="' . $accounting->account_number . '"</td></tr>';
	print '<tr><td>' . $langs->trans ( "Label" ) . '</td>';
	print '<td><input name="Label" size="70" value="' . $accounting->label . '"</td></tr>';
	print '<tr><td>' . $langs->trans ( "Accountparent" ) . '</td>';
	print '<td>';
	print $htmlacc->select_account_parent ( $accounting->account_parent, 'AccountParent' );
	print '</td></tr>';
	print '<tr><td>' . $langs->trans ( "Pcgtype" ) . '</td>';
	print '<td>';
	print $htmlacc->select_pcgtype ( $accounting->pcg_type, 'pcgType' );
	print '</td></tr>';
	print '<tr><td>' . $langs->trans ( "Pcgsubtype" ) . '</td>';
	print '<td>';
	print $htmlacc->select_pcgsubtype ( $accounting->pcg_subtype, 'pcgSubType' );
	print '</td></tr>';
	
	print '</table>';
	
	print '<br><center><input class="button" type="submit" value="' . $langs->trans ( "Save" ) . '"> &nbsp; &nbsp; ';
	print '<input class="button" type="submit" name="cancel" value="' . $langs->trans ( "Cancel" ) . '"></center';
	
	print '</form>';
} 
else if ($id) 
{
	$rowid = $id;
	$account = $accounting->fetch ( $rowid );
	
	if ($account > 0) {
		dol_htmloutput_mesg ( $mesg );
		
		$head = account_prepare_head($accounting);
    
    dol_fiche_head($head,'card',$langs->trans('AccountAccounting'),0,'billr');
		
		if ($action == 'update') 
    {
			// WYSIWYG Editor
			$htmlacc = new FormVentilation ( $db );
			
			require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
			
			$soc = new Societe ( $db );
			if ($object->socid) {
				$soc->fetch ( $object->socid );
			}
			
			print_fiche_titre ( $langs->trans ( "UpdateAccount" ) );
			
			print '<form name="update" action="' . $_SERVER ["PHP_SELF"] . '" method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
			print '<input type="hidden" name="action" value="edit">';
			print '<input type="hidden" name="id" value="' . $id . '">';
			
			print '<table class="border" width="100%">';
			
			print '<tr><td width="25%">' . $langs->trans ( "AccountNumber" ) . '</td>';
			print '<td><input name="AccountNumber" size="30" value="' . $accounting->account_number . '"</td></tr>';
			print '<tr><td>' . $langs->trans ( "Label" ) . '</td>';
			print '<td><input name="Label" size="70" value="' . $accounting->label . '"</td></tr>';
			print '<tr><td>' . $langs->trans ( "Accountparent" ) . '</td>';
			print '<td>';
			print $htmlacc->select_account_parent ( $accounting->account_parent, 'AccountParent' );
			print '</td></tr>';
			print '<tr><td>' . $langs->trans ( "Pcgtype" ) . '</td>';
			print '<td>';
			print $htmlacc->select_pcgtype ( $accounting->pcg_type, 'pcgType' );
			print '</td></tr>';
			print '<tr><td>' . $langs->trans ( "Pcgsubtype" ) . '</td>';
			print '<td>';
			print $htmlacc->select_pcgsubtype ( $accounting->pcg_subtype, 'pcgSubType' );
			print '</td></tr>';
			print '<tr><td>' . $langs->trans ( "Active" ) . '</td>';
			print '<td>';
			if (empty ( $accounting->active )) 
      {
				print '<a href="' . $_SERVER ["PHP_SELF"] . '?id=' . $accounting->id . '&action=enable">';
				print img_picto ( $langs->trans ( "Disabled" ), 'switch_off' );
				print '</a>';
			} 
      else 
      {
				print '<a href="' . $_SERVER ["PHP_SELF"] . '?id=' . $accounting->id . '&action=disable">';
				print img_picto ( $langs->trans ( "Activated" ), 'switch_on' );
				print '</a>';
			}
			
			print '</table>';
			
			print '<br><center><input type="submit" class="button" value="' . $langs->trans ( "Save" ) . '"> &nbsp; ';
			print '<input type="submit" name="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '">';
			print '</center>';
			
			print '</form>';
			
			print '</div>';
		} 
    else 
    {
			print '<table class="border" width="100%">';
			
			print '<tr><td width="25%">' . $langs->trans ( "AccountNumber" ) . '</td>';
			print '<td>' . $accounting->account_number . '</td></tr>';
			print '<tr><td>' . $langs->trans ( "Label" ) . '</td>';
			print '<td>' . $accounting->label . '</td></tr>';
			print '<tr><td>' . $langs->trans ( "Accountparent" ) . '</td>';
			print '<td>' . $accounting->account_parent . '</td></tr>';
			print '<tr><td>' . $langs->trans ( "Pcgtype" ) . '</td>';
			print '<td>' . $accounting->pcg_type . '</td></tr>';
			print '<tr><td>' . $langs->trans ( "Pcgsubtype" ) . '</td>';
			print '<td>' . $accounting->pcg_subtype . '</td></tr>';
			print '<tr><td>' . $langs->trans ( "Active" ) . '</td>';
			print '<td>';
			
			if (empty ( $accounting->active )) 
      {
				print img_picto ( $langs->trans ( "Disabled" ), 'switch_off' );
			} 
      else 
      {
				print img_picto ( $langs->trans ( "Activated" ), 'switch_on' );
			}
			
			print '</td></tr>';
			
			print '</table>';
			
			print '</div>';
			
			/*
       * Barre d'actions
       */
			
			print '<div class="tabsAction">';
			
			if ($user->rights->accountingex->admin) 
      {
				print '<a class="butAction" href="' . $_SERVER ["PHP_SELF"] . '?action=update&id=' . $id . '">' . $langs->trans ( 'Modify' ) . '</a>';
			} 
      else 
      {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag ( $langs->trans ( "NotAllowed" ) ) . '">' . $langs->trans ( 'Modify' ) . '</a>';
			}
			
      if ($user->rights->accountingex->admin) 
      {
				print '<a class="butActionDelete" href="' . $_SERVER ["PHP_SELF"] . '?action=delete&id=' . $id . '">' . $langs->trans ( 'Delete' ) . '</a>';
			} 
      else 
      {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag ( $langs->trans ( "NotAllowed" ) ) . '">' . $langs->trans ( 'Delete' ) . '</a>';
			}
			
			print '</div>';
		}
	} else {
		dol_print_error ( $db );
	}
}

llxFooter ();

$db->close ();
?>