<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013-2014 Florian Henry	      <florian.henry@open-concept.pro>
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
 *
 */

/**
    \file       htdocs/accountingex/admin/index.php
    \ingroup    Accounting Expert
		\brief      Page administration du module
*/

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
dol_include_once("/core/lib/admin.lib.php");
dol_include_once("/accountingex/core/lib/account.lib.php");

$langs->load("compta");
$langs->load("bills");
$langs->load('admin');
$langs->load('accountingex@accountingex');

// Securite accès client
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();

$action=GETPOST('action','alpha');

/*
 * Affichage page
 *
 */
 
if ($action == 'delete')
{
	if (! dolibarr_del_const($db, $_GET['constname'],$conf->entity));
	{
		print $db->error();
	}
}
 
if ($action == 'update' || $action == 'add')
{
	$constname = GETPOST('constname','alpha');
	$constvalue = GETPOST('constvalue','alpha');
	$consttype = GETPOST('consttype','alpha');
	$constnote = GETPOST('constnote','alpha');

	$res = dolibarr_set_const($db, $constname, $constvalue, $consttype, 0, $constnote, $conf->entity);

	if (! $res > 0) $error++;

 	if (! $error)
    {
        $mesg = "<font class=\"ok\">".$langs->trans("SetupSaved")."</font>";
    }
    else
    {
        $mesg = "<font class=\"error\">".$langs->trans("Error")."</font>";
    }
}
/*
 * Affichage page
 */

llxHeader();

$form = new Form($db);

print_fiche_titre($langs->trans('ConfigAccountingExpert'));

$head = admin_account_prepare_head(null);
		
dol_fiche_head($head,'journal',$langs->trans("Configuration"),0,'cron');

/*
 *  Params
 *
 */
$list=array('ACCOUNTINGEX_SELL_JOURNAL',
            'ACCOUNTINGEX_PURCHASE_JOURNAL',
            'ACCOUNTINGEX_SOCIAL_JOURNAL',
            'ACCOUNTINGEX_CASH_JOURNAL',
            'ACCOUNTINGEX_MISCELLANEOUS_JOURNAL'
);

$num=count($list);
if ($num)
{
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td colspan="3">'.$langs->trans('Journaux').'</td>';
	print "</tr>\n";
}

foreach ($list as $key)
{
	$var=!$var;

	print '<form action="index.php" method="POST">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="consttype" value="string">';
	print '<input type="hidden" name="constname" value="'.$key.'">';
	
	print '<tr '.$bc[$var].' class="value">';

	// Param
	$label = $langs->trans($key); 
	print '<td>'.$label;
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

dol_htmloutput_mesg($mesg);

$db->close();

llxFooter();
?>