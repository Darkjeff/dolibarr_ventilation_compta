<?PHP
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005      Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013 Florian Henry	  <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * $Id: liste.php,v 1.12 2011/07/31 22:23:31 eldy Exp $
 */

/**
        \file       htdocs/compta/param/comptes/liste.php
        \ingroup    compta
        \brief      Onglet de gestion de parametrages des ventilations
        \version    $Revision: 1.12 $
*/

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");


llxHeader('','Compta - Grand Livre');

$page = $_GET["page"];
$sortorder = $_GET["sortorder"];
$sortfield = $_GET["sortfield"];
if ($sortorder == "") $sortorder="ASC";
if ($sortfield == "") $sortfield="bk.rowid";

$offset = $conf->liste_limit * $page ;

/*
 * Mode Liste
 *
 *
 *
 */

$sql = "SELECT bk.rowid, bk.doc_date, bk.doc_type, bk.doc_ref, bk.code_tiers, bk.numero_compte , bk.label_compte, bk.debit , bk.credit, bk.montant , bk.sens ";

$sql .= " FROM ".MAIN_DB_PREFIX."bookkeeping as bk";

if (dol_strlen(trim($_GET["search_doc_type"])) )
{

  $sql .= " WHERE bk.doc_type LIKE '%".$_GET["search_doc_type"]."%'";

  if ( dol_strlen(trim($_GET["search_doc_ref"])))
    {
      $sql .= " AND bk.doc_ref LIKE '%".$_GET["search_doc_ref"]."%'";
    }

}
else
{
  if ( dol_strlen(trim($_GET["search_doc_ref"])))
    {
      $sql .= " WHERE bk.doc_ref LIKE '%".$_GET["search_doc_ref"]."%'";
    }
}


$sql .= " ORDER BY $sortfield $sortorder " . $db->plimit($conf->liste_limit+1, $offset);

$resql = $db->query($sql);
if ($resql)
{
  $num = $db->num_rows($resql);
  $i = 0;

  print_barre_liste("Grand Livre", $page, "liste.php", "", $sortfield, $sortorder, '', $num);

  print '<table class="liste">';
  print '<tr class="liste_titre">';
  print_liste_field_titre($langs->trans("doctype"),"liste.php","bk.doc_type");
  print_liste_field_titre($langs->trans("docdate"),"liste.php","bk.doc_date");
  print_liste_field_titre($langs->trans("docref"),"liste.php","bk.doc_ref");
  print_liste_field_titre($langs->trans("numerocompte"),"liste.php","bk.numero_compte");
  print_liste_field_titre($langs->trans("code_tiers"),"liste.php","bk.code_tiers");
  print_liste_field_titre($langs->trans("labelcompte"),"liste.php","bk_label_compte");
  print_liste_field_titre($langs->trans("debit"),"liste.php","bk.debit");
  print_liste_field_titre($langs->trans("credit"),"liste.php","bk.credit");
  print_liste_field_titre($langs->trans("montant"),"liste.php","bk.montant");
  print_liste_field_titre($langs->trans("sens"),"liste.php","bk.sens");
  print "</tr>\n";

  print '<tr class="liste_titre">';
  print '<form action="liste.php" method="GET">';
  print '<td><input type="text" name="search_doc_type" value="'.$_GET["search_doc_type"].'"></td>';
  print '<td>&nbsp;</td>';
  print '<td><input type="text" name="search_doc_refe" value="'.$_GET["search_doc_ref"].'"></td>';
  print '<td>&nbsp;</td>';
  print '<td>&nbsp;</td>';
  print '<td>&nbsp;</td>';
  print '<td>&nbsp;</td>';
  print '<td>&nbsp;</td>';
  print '<td>&nbsp;</td>';
  print '<td align="right">';
  print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
  print '</td>';
  print '</form>';
  print '</tr>';

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
      print '<td>'.$obj->numero_compte.'</td>';
      print '<td>'.$obj->code_tiers.'</td>';
      print '<td>'.$obj->label_compte.'</td>';
      print '<td>'.$obj->debit.'</td>';
      print '<td>'.$obj->credit.'</td>';
      print '<td>'.$obj->montant.'</td>';
      print '<td>'.$obj->sens.'</td>';
      print '<td align="right" width="100">';
      

      print '</td>';
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

llxFooter("<em>Derni&egrave;re modification $Date: 2011/07/31 22:23:31 $ r&eacute;vision $Revision: 1.12 $</em>");
?>
