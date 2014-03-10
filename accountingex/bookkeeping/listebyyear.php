<?PHP
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Florian Henry	      <florian.henry@open-concept.pro>
 * Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
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
 * \file       accountingex/bookkeeping/listebyyear.php
 * \ingroup    Accounting Expert
 * \brief      Grand livre par année
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
dol_include_once("/core/lib/date.lib.php");
dol_include_once("/accountingex/core/lib/account.lib.php");

// Langs
$langs->load("accountingex@accountingex");

// Filter
$year=$_GET["year"];
if ($year == 0 )
{
  $year_current = strftime("%Y",time());
  $year_start = $year_current;
} 
else 
{
  $year_current = $year;
  $year_start = $year;
}

llxHeader('',$langs->trans("Bookkeeping"));

$textprevyear="<a href=\"listebyyear.php?year=" . ($year_current-1) . "\">".img_previous()."</a>";
$textnextyear=" <a href=\"listebyyear.php?year=" . ($year_current+1) . "\">".img_next()."</a>";


print_fiche_titre("Grand Livre $textprevyear ".$langs->trans("Year")." $year_start $textnextyear");


/*
 * Mode Liste
 *
 *
 *
 */

$sql = "SELECT bk.rowid, bk.doc_date, bk.doc_type, bk.doc_ref, bk.code_tiers, bk.numero_compte , bk.label_compte, bk.debit , bk.credit, bk.montant , bk.sens ";
$sql .= " FROM ".MAIN_DB_PREFIX."bookkeeping as bk";
//$sql .= " WHERE bk.doc_date >= '".$db->idate(dol_get_first_day($y,1,false))."'";
//$sql .= "  AND bk.doc_date <= '".$db->idate(dol_get_last_day($y,12,false))."'";
$sql .= " ORDER BY bk.doc_date ";

$resql = $db->query($sql);
if ($resql)
{
  $num = $db->num_rows($resql);
  $i = 0;

 

  print '<table class="liste" width="100%">';
  print '<tr class="liste_titre">';
  print_liste_field_titre($langs->trans("Doctype"));
  print_liste_field_titre($langs->trans("Docdate"));
  print_liste_field_titre($langs->trans("Docref"));
  print_liste_field_titre($langs->trans("Account"));
  print_liste_field_titre($langs->trans("Code_tiers"));
  print_liste_field_titre($langs->trans("Labelcompte"));
  print_liste_field_titre($langs->trans("Debit"));
  print_liste_field_titre($langs->trans("Credit"));
  print_liste_field_titre($langs->trans("Amount"));
  print "</tr>\n";

  $var=True;

  while ($i < min($num,$conf->liste_limit))
    {
      $obj = $db->fetch_object($resql);
      $var=!$var;

      print "<tr $bc[$var]>";

      print '<td><a href="./fiche.php?action=update&id='.$obj->rowid.'">';
	    print img_edit();
	    print '</a>&nbsp;'.$obj->doc_type.'</td>'."\n";
	    print '<td>'.dol_print_date($db->jdate($obj->doc_date)).'</td>';
      print '<td>'.$obj->doc_ref.'</td>';
      print '<td>'.length_accountg($obj->numero_compte).'</td>';
      print '<td>'.length_accounta($obj->code_tiers).'</td>';
      print '<td>'.$obj->label_compte.'</td>';
      print '<td align="right">'.$obj->debit.'</td>';
      print '<td align="right">'.$obj->credit.'</td>';
      print '<td align="right">'.$obj->montant.'</td>';
            
      print "</tr>\n";
      $i++;
    }
  print "</table>";
  $db->free($resql);
}
else
{
  dol_print_error($db);
}

$db->close();

llxFooter();
?>
