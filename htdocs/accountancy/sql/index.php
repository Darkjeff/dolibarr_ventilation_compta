<?php
/* Copyright (C) 2017     Elarifr        <github@accedinfo.com>
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
 * \file    htdocs/accountancy/index.php
 * \ingroup Advanced accountancy
 * \brief   SQL Update for accountancy dev group
 */
require '../../main.inc.php';

$action = GETPOST('action');
$filename="../sql/update.sql";

// Langs
$langs->load("other");
$langs->load("main");
$langs->load("accountancy");

// Security check
if ($user->societe_id > 0)
	accessforbidden();
/*
if (! $user->rights->accounting->sqlupdate)              // Darkjeff & Alesx we need to add rights in accountancy before check this rights !!!
    accessforbidden();
*/

/*
 * Actions
 */


if ($action=="update")
{
     $updated_sql ="";
    // process update
    $lines = file($filename);
    // Loop through each line
    foreach ($lines as $line)
    {
    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '')
        continue;
    // Add this line to the current segment
    $templine .= $line;
    // If it has a semicolon at the end, it's the end of the query
    if (substr(trim($line), -1, 1) == ';')
    {
        $updated_sql .=  $templine."<br />\n";
        // Perform the query
        //print   $templine."<b />\n";
        //mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
        $db->begin();   // Debut transaction
        $resql= $db->query($templine);
        $resql= $db->commit();
        $updated_sql .=  $resql."<br /><br />\n";
        // Reset temp variable to empty
        $templine = '';
    }
    }
     $updated_sql .= "<b>Update processed. check if error occured !</b><br />\n";


// delete file update.sql
$updated_sql .= "<br />\nNow i have deleted : " . dirname(__FILE__) ."/update.sql<br />\n";
unlink(dirname(__FILE__) . "/update.sql");

}
/*
 * View
 */

llxHeader('', 'AccountancySqlUpdate', '');

print load_fiche_titre($langs->trans("AccountancySqlUpdate"), '', 'title_accountancy');

if ($updated_sql != ""){print $updated_sql;}

if (file_exists($filename))
{
     echo "ok i have an update.sql file to run for accountancy.<br />After applying update, i should delete this file !";
	print "<br>\n";

     print '<a class="butAction" href="index.php?action=update">' . $langs->trans("UpdateSql") . '</a>';

}
else
{
     print "there is no sql file to update... nice day there is no change in db today :)";

}

print "<br>\n";
print "<br>\n";
print "<br>\n";
print "SQL Update provided to Dolibarr Accountancy Subscription 2017 by <a href='http://accedinfo.com'>Elbaz Ari - Accedinfo.com</a>.<br /> Take contact with Olivier to get a free copy of ExtraBackup and Extract update module to manage easy update. Elarifr";
print "This tool is planned to be removed in final version of accountancy. This is why we don't have any visible menu";
print "<br>\n";

dol_fiche_end();

print "<br>\n";

print "<br>\n";
// End of page
llxFooter();
$db->close();