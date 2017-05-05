<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <2017>  saasprov@gmail.com <saasprov.ma>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    admin/setup.php
 * \ingroup stripe
 * \brief   Example module setup page.
 *
 * Put detailed description here.
 */

// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/stripe.lib.php';
//require_once "../class/myclass.class.php";
// Translations
$langs->load("stripe@stripe");
$langs->load("admin");
$langs->load("other");

if (! $user->admin) accessforbidden();

$action = GETPOST('action','alpha');

if ($action == 'setvalue' && $user->admin)
{
	$db->begin();
    $result=dolibarr_set_const($db, "TEST_SECRET_KEY",GETPOST('TEST_SECRET_KEY','alpha'),'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;
    $result=dolibarr_set_const($db, "TEST_PUBLISHABLE_KEY",GETPOST('TEST_PUBLISHABLE_KEY','alpha'),'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;
    $result=dolibarr_set_const($db, "LIVE_SECRET_KEY",GETPOST('LIVE_SECRET_KEY','alpha'),'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;
    $result=dolibarr_set_const($db, "LIVE_PUBLISHABLE_KEY",GETPOST('LIVE_PUBLISHABLE_KEY','alpha'),'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;
	// $result=dolibarr_set_const($db, "LIVE_ENABLED",GETPOST('LIVE_ENABLED','alpha'),'chaine',0,'',$conf->entity);
    // if (! $result > 0) $error++;
	
	
	//Activate Ask For Preferred Shipping Method

	
	if (! $error)
  	{
  		$db->commit();
  		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
  	}
  	else
  	{
  		$db->rollback();
		dol_print_error($db);
    }
}
if ($action=="setasklive") {
	$liveenable = GETPOST('value','int');
	$res = dolibarr_set_const($db, "LIVE_ENABLED", $liveenable,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;
	if (! $error)
	{
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	}
	else
	{
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

/*
 * View
 */
$page_name = "StripeSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = stripeAdminPrepareHead();
dol_fiche_head(
	$head,
	'settings',
	$langs->trans("Stripe"),
	0,
	"stripe@stripe"
);

// Setup page goes here
echo $langs->trans("StripeSetupPage");

// Test if php curl exist
if (! function_exists('curl_version'))
{
	$langs->load("errors");
	setEventMessages($langs->trans("ErrorPhpCurlNotInstalled"), null, 'errors');
}

print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvalue">';

print '<br>';
print '<br>';

print '<table class="noborder" width="100%">';

// Account Parameters
$var=true;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("TestSecretKey").'</td><td>';
print '<input size="32" type="text" name="TEST_SECRET_KEY" value="'.$conf->global->TEST_SECRET_KEY.'">';
print ' &nbsp; '.$langs->trans("Example").': sk_test_1D4FUKWdfmQPBb0ZWcOylJ2n ';
print '</td></tr>';

$var=!$var;

print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("TestPublishableKey:").'</td><td>';
print '<input size="32" type="text" name="TEST_PUBLISHABLE_KEY" value="'.$conf->global->TEST_PUBLISHABLE_KEY.'">';
print ' &nbsp; '.$langs->trans("Example").': pk_test_doecWIofywF6SRJ7tsGj30Ss ';
print '</td></tr>';

$var=!$var;

print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LiveSecretKey").'</td><td>';
print '<input size="32" type="text" name="LIVE_SECRET_KEY" value="'.$conf->global->LIVE_SECRET_KEY.'">';
print ' &nbsp; '.$langs->trans("Example").': sk_live_xZmsGdY2chMl1eX02nQLXPyT ';
print '</td></tr>';

$var=!$var;

print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LivePublishableKey").'</td><td>';
print '<input size="32" type="text" name="LIVE_PUBLISHABLE_KEY" value="'.$conf->global->LIVE_PUBLISHABLE_KEY.'">';
print ' &nbsp; '.$langs->trans("Example").': pk_live_hFbNtJmKEJb3njN7mSSBUKxX  ';
print '</td></tr>';

$var=!$var;



print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LiveEnabled").'</td><td>';

if (!empty($conf->global->LIVE_ENABLED))
{
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=setasklive&value=0">';
	print img_picto($langs->trans("Activated"),'switch_on');

}
else
{
	print '<a href="'.$_SERVER['PHP_SELF'].'?action=setasklive&value=1">';
	print img_picto($langs->trans("Disabled"),'switch_off');
}

print '</td></tr>';

print '</table>';

dol_fiche_end();

print '<div class="center"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></div>';

print '</form>';

llxFooter();
