<?php
/* Copyright (C) 2013-2014 Olivier Geffroy			<jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro		<alexandre.spangaro@gmail.com>
 * Copyright (C) 2014	   Florian Henry			<florian.henry@open-concept.pro>
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
 * \file		accountingex/admin/about.php
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

$langs->load('main');
$langs->load('accountingex@accountingex');

// Security check
if ($user->societe_id > 0)
	accessforbidden();
if (! $user->rights->accountingex->admin)
	accessforbidden();
	
/*
 * View
 */

llxHeader();

print_fiche_titre($langs->trans('ConfigAccountingExpert'));

$head = admin_account_prepare_head(null);

dol_fiche_head($head, 'about', $langs->trans("Configuration"), 0, 'cron');

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre"><td colspan="2">' . $langs->trans("Contributors") . '</td>';
print '</tr>';

// Olivier Geffroy
print '<tr><td><img src="../img/jeffinfo.png" width="250"></td>';
print '<td><b>Olivier Geffroy</b>&nbsp;-&nbsp;Consultant Informatique';
print '<br>Jeffinfo - 59660 Merville<br>' . $langs->trans("Email") . ' : jeff@jeffinfo.com <br>' . $langs->trans("Phone") . ' : +33 6 08 63 27 40';
print '<br><a target="_blank" href="http://jeffinfo.com">http://www.jeffinfo.com/</a>';
print '<br><br><a title="Jeffinfo Facebook" target="_blank" href="https://www.facebook.com/pages/Jeffinfo/165397806821029"><img src="../img/fb.png" width="20"></a>&nbsp;';
print '<a target="_blank" href="http://twiter.com/zendsi"><img src="../img/tweet.png" width="20"></a>&nbsp;';
print '<a target="_blank" href="https://plus.google.com/+OlivierGeffroy/posts"><img src="../img/google+.png" width="20"></a>';
print '</td></tr>';

print '<tr><td>&nbsp;</td></tr>';

// Alexandre Spangaro
print '<tr><td><img src="../img/fidurex.jpg" width="250"></td>';
print '<td><b>Alexandre Spangaro</b>&nbsp;-&nbsp;Comptable';
print '<br>Cabinet Fidurex - Expertise comptable, commissariat aux comptes, audit, social.<br>' . $langs->trans("Email") . ' : aspangaro@fidurex.fr <br>' . $langs->trans("Phone") . ' : +33 3 29 79 04 42';
print '<br><br><a target="_blank" href="http://twiter.com/alexspangaro"><img src="../img/tweet.png" width="20"></a>';
print '</td></tr>';

print '<tr><td>&nbsp;</td></tr>';

// Ari Elbaz - Accedinfo
print '<tr align="left"><td><img src="../img/accedinfo.jpg" width="250"></td>';
print '<td><b>Ari Elbaz</b>';
print '<br>Accedinfo.com<br>67000 Strasbourg<br>' . $langs->trans("Phone") . ' : +33 3 88 33 77 47';
print '<br><a target="_blank" href="http://accedinfo.com">http://www.accedinfo.com/</a>';
print '<br><br><a title="Accedinfo.com Facebook" target="_blank" href="http://www.facebook.com/accedinfosav"><img src="../img/fb.png" width="20"></a>&nbsp;';
print '<a target="_blank" href="http://twiter.com/elarifr"><img src="../img/tweet.png" width="20"></a>&nbsp;';
print '<a target="_blank" href="https://plus.google.com/109875408706351786851"><img src="../img/google+.png" width="20"></a>';
print '</td></tr>';

print '<tr><td>&nbsp;</td></tr>';

// Florian HENRY - Open-concept.pro
print '<tr><td><img src="http://demo.open-concept.pro/prestashop/img/logo.jpg" width="250"></td>';
print '<td><b>Florian Henry</b>&nbsp;-&nbsp;Intégrateur/développeur Solution Open-Source';
print '<br>Florian HENRY - Open-Concept.pro  <BR>' . $langs->trans("Email") . ' : florian.henry@open-concept.pro <BR>' . $langs->trans("Phone") . ' : +33 6 03 76 48 07';
print '<br><a target="_blank" href="http://www.open-concept.pro">http://www.open-concept.pro</a>';
print '<br><br><a target="_blank" href="http://twitter.com/_open_concept_"><img src="../img/tweet.png" width="20"></a>';
print '</td></tr>';


print '</table>';
print '<br>';
// Investors
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td colspan="2">' . $langs->trans("Investors") . '</td>';
print '</tr>';

print '<tr><td>Jeffinfo</td>';
print '<td>2000 €</td></tr>';
print '<tr><td>Nord Anim</td>';
print '<td>120 €</td></tr>';
print '<tr><td>Hydroflex</td>';
print '<td>120 €</td></tr>';
print '<tr><td>Asysteo</td>';
print '<td>120 €</td></tr>';
print '<tr><td>Fournisseur Medical</td>';
print '<td>120 €</td></tr>';

print '</table>';

dol_htmloutput_mesg($mesg);

llxFooter();
$db->close();