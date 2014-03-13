<?php
/* Copyright (C) 2013-2014 Florian Henry	      <florian.henry@open-concept.pro>
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
    \file       accountingex/admin/about.php
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

$langs->load('accountingex@accountingex');

// Securite accès client
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();

$action=GETPOST('action','alpha');

/*
 * View
 */

llxHeader();

$form=new Form($db);

print_fiche_titre($langs->trans('ConfigAccountingExpert'));

$head = admin_account_prepare_head($accounting);
		
dol_fiche_head($head,'card',$langs->trans("About"),0,'cron');



dol_htmloutput_mesg($mesg);

$db->close();

llxFooter();
?>