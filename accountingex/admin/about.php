<?php
/* Copyright (C) 2013-2014 Olivier Geffroy			<jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro		<alexandre.spangaro@gmail.com>
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

$langs->load('main');
$langs->load('accountingex@accountingex');

// Securité accès client
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();

/*
 * View
 */

llxHeader();

print_fiche_titre($langs->trans('ConfigAccountingExpert'));

$head = admin_account_prepare_head(null);
		
dol_fiche_head($head,'about',$langs->trans("Configuration"),0,'cron');

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("Authors").'</td>';
print '</tr>';

// Alexandre Spangaro
print '<tr><td><img src="../img/fidurex.jpg" width="250"></td>';
print '<td><b>Alexandre Spangaro</b>&nbsp;-&nbsp;Comptable';
print '<br>Cabinet Fidurex - 29 Boulevard de la Rochelle - 55000 Bar le Duc<br>Tél : 03.29.79.04.42 - @ : aspangaro AT fidurex DOT fr';
print '<br><br><a target="_blank" href="http://twiter.com/alexspangaro"><img src="../img/tweet.png" width="20"></a>';
print '</td></tr>';

print '<tr><td>&nbsp;</td></tr>';

// Ari Elbaz - Accedinfo
print '<tr align="left"><td><img src="../img/accedinfo.jpg" width="250"></td>';
print '<td><b>Ari Elbaz</b>';
print '<br>Accedinfo.com<br>67000 Strasbourg<br>Tél : 03.88.33.77.47<br><br>';
print '<a title="Accedinfo.com Facebook" target="_blank" href="http://www.facebook.com/accedinfosav"><img src="../img/fb.png" width="20"></a>&nbsp;';
print '<a target="_blank" href="http://twiter.com/elarifr"><img src="../img/tweet.png" width="20"></a>&nbsp;';
print '<a target="_blank" href="https://plus.google.com/109875408706351786851"><img src="../img/google+.png" width="20"></a>';
print '</td></tr>';

  
dol_htmloutput_mesg($mesg);

$db->close();

llxFooter();
?>