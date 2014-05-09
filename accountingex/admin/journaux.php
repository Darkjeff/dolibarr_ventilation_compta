<?php
/* Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro	<alexandre.spangaro@gmail.com>
 * Copyright (C) 2014 	   Florian Henry		<florian.henry@open-concept.pro>
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
 *
 */

/**
 * \file htdocs/accountingex/admin/journaux.php
 * \ingroup Accounting Expert
 * \brief Setup page to configure accounting expert module
 */

// Dolibarr environment
$res = @include ("../main.inc.php");
if (! $res && file_exists("../main.inc.php"))
	$res = @include ("../main.inc.php");
if (! $res && file_exists("../../main.inc.php"))
	$res = @include ("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php"))
	$res = @include ("../../../main.inc.php");
if (! $res)
	die("Include of main fails");
	
	// Class
dol_include_once("/core/lib/admin.lib.php");
dol_include_once("/accountingex/core/lib/account.lib.php");

$langs->load('accountingex@accountingex');

// Security check
if ($user->societe_id > 0)
	accessforbidden();
if (! $user->rights->accountingex->admin)
	accessforbidden();

$action = GETPOST('action', 'alpha');

// Other parameters ACCOUNTINGEX_*
$list = array (
		'ACCOUNTINGEX_SELL_JOURNAL',
		'ACCOUNTINGEX_PURCHASE_JOURNAL',
		'ACCOUNTINGEX_SOCIAL_JOURNAL',
		'ACCOUNTINGEX_CASH_JOURNAL',
		'ACCOUNTINGEX_MISCELLANEOUS_JOURNAL' 
);

/*
 * Actions
 */

if ($action == 'update') {
	$error = 0;
	
	foreach ( $list as $constname ) {
		$constvalue = GETPOST($constname, 'alpha');
		
		if (! dolibarr_set_const($db, $constname, $constvalue, 'chaine', 0, '', $conf->entity)) {
			$error ++;
		}
	}
	
	if (! $error) {
		setEventMessage($langs->trans("SetupSaved"));
	} else {
		setEventMessage($langs->trans("Error"), 'errors');
	}
}

/*
 * View
 */

llxHeader();

$form = new Form($db);

print_fiche_titre($langs->trans('ConfigAccountingExpert'));

$head = admin_account_prepare_head(null);

dol_fiche_head($head, 'journal', $langs->trans("Configuration"), 0, 'cron');

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td colspan="3">' . $langs->trans('Journaux') . '</td>';
print "</tr>\n";

foreach ( $list as $key ) {
	$var = ! $var;
	
	print '<tr ' . $bc[$var] . ' class="value">';
	
	// Param
	$label = $langs->trans($key);
	print '<td><label for="' . $key . '">' . $label . '</label></td>';
	
	// Value
	print '<td>';
	print '<input type="text" size="20" id="' . $key . '" name="' . $key . '" value="' . $conf->global->$key . '">';
	print '</td></tr>';
}

print '</form>';
print "</table>\n";

print '<br /><div style="text-align:center"><input type="submit" class="button" value="' . $langs->trans('Modify') . '" name="button"></div>';

llxFooter();
$db->close();