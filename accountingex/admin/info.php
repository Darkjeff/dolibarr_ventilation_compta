<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013      Alexandre Spangaro   <alexandre.spangaro@gmail.com> 
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
 * 	\file       htdocs/accountingex/admin/info.php
 * 	\ingroup    Accounting Expert
 * 	\brief      Page to show an account information
 */

 $res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
dol_include_once ( "/accountingex/core/lib/account.lib.php");
dol_include_once ( "/accountingex/class/accountingaccount.class.php");
dol_include_once ( "/core/lib/functions2.lib.php");

$langs->load("accountingex@accountingex");

// Security check
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();


/*
 * View
 */

llxHeader();

if ($id)
{
	$object = new AccountingAccount($db);
	$object->fetch($id);
	$object->info($id);
	
	$head = account_prepare_head($object);
	
	dol_fiche_head($head, 'info', $langs->trans("AccountCard"), 0, 'billr');

    print '<table width="100%"><tr><td>';
    dol_print_object_info($object);
    print '</td></tr></table>';
      
    print '</div>';
}

$db->close();

llxFooter();
?>
