<?php
/* Copyright (C) 2013-2014 Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2013-2015 Alexandre Spangaro	<alexandre.spangaro@gmail.com>
 * Copyright (C) 2014	   Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2014      Marcos García        <marcosgdf@gmail.com>
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
 * \file		htdocs/accountingex/admin/export.php
 * \ingroup		Accounting Expert
 * \brief		Setup page to configure accounting expert module
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

$langs->load("compta");
$langs->load("bills");
$langs->load('admin');
$langs->load('accountingex@accountingex');

// Security check
if ($user->societe_id > 0)
	accessforbidden();
if (! $user->rights->accountingex->admin)
	accessforbidden();

$action = GETPOST('action', 'alpha');

// Other parameters ACCOUNTINGEX_*
// All parameters are moved into ACCOUNTING_* in Dolibarr 3.7
$list = array (
		'ACCOUNTINGEX_SEPARATORCSV',
		'ACCOUNTINGEX_EXP_DATE',
		'ACCOUNTINGEX_EXP_PIECE',
		'ACCOUNTINGEX_EXP_GLOBAL_ACCOUNT',
		'ACCOUNTINGEX_EXP_LABEL',
		'ACCOUNTINGEX_EXP_AMOUNT',
		'ACCOUNTINGEX_EXP_DEVISE'
);

/*
 * Actions
 */
if ($action == 'update')
{
	$error = 0;
	
	$modelcsv = GETPOST('modelcsv', 'int');
	
	if (! empty($modelcsv))
	{
		if (! dolibarr_set_const($db, 'ACCOUNTINGEX_MODELCSV', $modelcsv, 'chaine', 0, '', $conf->entity))
		{
			$error ++;
		}
	}
	else
	{
		$error ++;
	}
	
	foreach ( $list as $constname )
	{
		$constvalue = GETPOST($constname, 'alpha');
			
		if (! dolibarr_set_const($db, $constname, $constvalue, 'chaine', 0, '', $conf->entity))
		{
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

dol_fiche_head($head, 'export', $langs->trans("Configuration"), 0, 'cron');

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder" width="100%">';
$var = true;

print '<tr class="liste_titre">';
print '<td colspan="2">' . $langs->trans("Modelcsv") . '</td>';
print '</tr>';

$var = ! $var;

print '<tr ' . $bc[$var] . '>';
print "<td width='50%'>" . $langs->trans("Selectmodelcsv") . "</td>";
if (! $conf->use_javascript_ajax)
{
	print '<td class="nowrap">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print "</td>";
}
else
{
	print '<td>';
	$listmodelcsv=array(
		'1'=>$langs->trans("Modelcsv_normal"),
		'2'=>$langs->trans("Modelcsv_CEGID")
	);
	print $form->selectarray("modelcsv",$listmodelcsv,$conf->global->ACCOUNTINGEX_MODELCSV,0);
	print '</td>';
}

print "</td></tr>";
print "</table>";

print "<br>\n";

/*
 *  Parameters
 */

$num = count($list);
if ($num)
{
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td colspan="3">' . $langs->trans('OtherOptions') . '</td>';
	print "</tr>\n";
	if ($conf->global->ACCOUNTINGEX_MODELCSV > 1) print '<tr><td colspan="2" bgcolor="red"><b>' . $langs->trans('OptionsDeactivated') . '</b></td></tr>';   
}

foreach ( $list as $key )
{
	$var = ! $var;
	
	print '<tr ' . $bc[$var] . ' class="value">';
		
	// Param
	$label = $langs->trans($key);
	print '<td width="50%">' . $label . '</td>';
		
	// Value
	print '<td>';
	print '<input type="text" size="20" name="' . $key . '" value="' . $conf->global->$key . '">';
	print '</td></tr>';
}

print '</form>';
print "</table>\n";

print '<br /><div align="center"><input type="submit" class="button" value="' . $langs->trans('Modify') . '" name="button"></div>';

llxFooter();
$db->close();