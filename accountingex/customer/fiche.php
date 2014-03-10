<?PHP
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013      Florian Henry	      <florian.henry@open-concept.pro>
 * Copyright (C) 2013      Alexandre Spangaro   <alexandre.spangaro@fidurex.fr> 
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
 *      \file       accountingex/customer/fiche.php
 *      \ingroup    Accounting Expert
 *      \brief      Page fiche ventilation
 */
 
// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
dol_include_once("/compta/facture/class/facture.class.php");

$langs->load("bills");
$langs->load("accountingex@accountingex");

$mesg = '';

if (!$user->rights->accountingex->access) accessforbidden();


/*
 * Actions
 */

if ($_POST["action"] == 'ventil' && $user->rights->accountingex->access)
{
  $sql = " UPDATE ".MAIN_DB_PREFIX."facturedet";
  $sql .= " SET fk_code_ventilation = ".$_POST["codeventil"];
  $sql .= " WHERE rowid = ".$_GET["id"];
  dol_syslog ( "Accountancy Expert :: Card Customer :: Update sql=" . $sql, LOG_DEBUG );

  $db->query($sql);
}

llxHeader("","","Fiche ventilation");

if ($cancel == $langs->trans("Cancel"))
{
  $action = '';
}
/*
 *
 *
 */

$sql = "SELECT a.rowid, a.account_number, a.label, a.fk_pcg_version";
$sql .= " , s.rowid, s.pcg_version";
$sql .= " FROM ".MAIN_DB_PREFIX."accountingaccount as a, ".MAIN_DB_PREFIX."accounting_system as s";
$sql .= " WHERE a.fk_pcg_version = s.pcg_version AND s.rowid=".$conf->global->CHARTOFACCOUNTS;
$sql .= " AND a.active = '1'";
$sql .= " ORDER BY a.account_number ASC";
dol_syslog ( "Accountancy Expert :: Card Customer :: Fetch sql=" . $sql, LOG_DEBUG );

$cgs = array();
$cgn = array();
$result = $db->query($sql);
if ($result)
{
  $num = $db->num_rows($result);
  $i = 0; 
  
  while ($i < $num)
    {
      $row = $db->fetch_row($result);
      $cgs[$row[0]] = $row[1] . ' ' . $row[2];
      $cgn[$row[2]] = $row[0];
      $i++;
    }
}

/*
 * Création
 *
 */
$form = new Form($db);
$facture_static=new Facture($db);

if($_GET["id"])
{
    $sql = "SELECT f.facnumber, f.rowid as facid, l.fk_product, l.description, l.price,";
    $sql .= " l.qty, l.rowid, l.tva_tx, l.remise_percent, l.subprice, p.accountancy_code_sell as code_sell,";
    $sql .= " ".$db->idate("l.date_start")." as date_start, ".$db->idate("l.date_end")." as date_end,";
    $sql .= " l.fk_code_ventilation ";
    $sql .= " FROM ".MAIN_DB_PREFIX."facturedet as l";
	  $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p ON p.rowid = l.fk_product";
    $sql .= " , ".MAIN_DB_PREFIX."facture as f";
    $sql .= " WHERE f.rowid = l.fk_facture AND f.fk_statut > 0 AND l.rowid = ".$_GET["id"];

    $result = $db->query($sql);

    if ($result)
    {
        $num_lignes = $db->num_rows($result);
        $i = 0;

        if ($num_lignes)
        {

            $objp = $db->fetch_object($result);


            
                print '<form action="fiche.php?id='.$_GET["id"].'" method="post">'."\n";
                print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
                print '<input type="hidden" name="action" value="ventil">';
            


            print_fiche_titre("Ventilation");

            print '<table class="border" width="100%">';

			      // Ref facture
            print '<tr><td>'.$langs->trans("Invoice").'</td>';
			      $facture_static->ref=$objp->facnumber;
			      $facture_static->id=$objp->facid;
			      print '<td>'.$facture_static->getNomUrl(1).'</td>';
            print '</tr>';

            print '<tr><td width="20%">'.$langs->trans("Line").'</td>';
            print '<td>'.nl2br($objp->description).'</td></tr>';
            print '<tr><td width="20%">'.$langs->trans("Account").'</td><td>';
            print $cgs[$objp->fk_code_ventilation];
            print '<tr><td width="20%">'.$langs->trans("NewAccount").'</td><td>';
            print $form->selectarray("codeventil",$cgs, $cgn[$objp->code_sell]);            
            print '</td></tr>';
            print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="'.$langs->trans("Update").'"></td></tr>';
            
            print '</table>';
            print '</form>';
        }
        else
        {
            print "Error";
        }
    }
    else
    {
        print "Error";
    }
}
else
{
    print "Error ID incorrect";
}

$db->close();

llxFooter();
?>
