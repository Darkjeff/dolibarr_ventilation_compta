<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013      Florian Henry	      <florian.henry@open-concept.pro>
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
 *
 */

/**
 *    \file       accountingex/customer/index.php
 *    \ingroup    Accounting Expert
 *    \brief      Page accueil clients ventilation comptable
 */
 // Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");

// Langs
$langs->load("compta");
$langs->load("bills");
$langs->load("other");
$langs->load("main");
$langs->load("accountingex@accountingex");

// Security check
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->access) accessforbidden();


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


/*
 * View
 */
llxHeader('',$langs->trans("CustomersVentilation"));

$textprevyear="<a href=\"index.php?year=" . ($year_current-1) . "\">".img_previous()."</a>";
$textnextyear=" <a href=\"index.php?year=" . ($year_current+1) . "\">".img_next()."</a>";

print_fiche_titre($langs->trans("CustomersVentilation")." ".$textprevyear." ".$langs->trans("Year")." ".$year_start." ".$textnextyear);

print '<td align="left"><br><b>'.$langs->trans("DescVentilCustomer").'</b></br></td>';

$sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."facturedet as fd";
$sql.= " , ".MAIN_DB_PREFIX."facture as f";
$sql.= " WHERE fd.fk_code_ventilation = 0";
$sql.= " AND f.rowid = fd.fk_facture AND f.fk_statut = 1;";

$result = $db->query($sql);
if ($result)
{
	$row = $db->fetch_row($result);
	$nbfac = $row[0];
	$db->free($result);
}

$y = $year_current ;

$var=true;

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=60>'.$langs->trans("Account").'</td>';
print '<td width=200 align="left">'.$langs->trans("Intitule").'</td>';
print '<td width=60 align="center">'.$langs->trans("JanuaryMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("FebruaryMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("MarchMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("AprilMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("MayMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("JuneMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("JulyMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("AugustMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("SeptemberMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("OctoberMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("NovemberMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("DecemberMin").'</td>';
print '<td width=60 align="center"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT IF(aa.account_number IS NULL, 'Non pointe', aa.account_number) AS 'code comptable',";
$sql .= "  IF(aa.label IS NULL, 'Non pointe', aa.label) AS 'Intitulé',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=1,fd.total_ht,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=2,fd.total_ht,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=3,fd.total_ht,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=4,fd.total_ht,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=5,fd.total_ht,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=6,fd.total_ht,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=7,fd.total_ht,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=8,fd.total_ht,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=9,fd.total_ht,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=10,fd.total_ht,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=11,fd.total_ht,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=12,fd.total_ht,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(fd.total_ht),2) as 'Total'";
$sql .= " FROM ".MAIN_DB_PREFIX."facturedet as fd";
$sql .= "  LEFT JOIN ".MAIN_DB_PREFIX."facture as f ON f.rowid = fd.fk_facture";
$sql .= "  LEFT JOIN ".MAIN_DB_PREFIX."accountingaccount as aa ON aa.rowid = fd.fk_code_ventilation";
$sql .= " WHERE f.datef >= '".$db->idate(dol_get_first_day($y,1,false))."'";
$sql .= "  AND f.datef <= '".$db->idate(dol_get_last_day($y,12,false))."'";
$sql .= " GROUP BY fd.fk_code_ventilation";

$resql = $db->query($sql);
if ($resql)
{
	$i = 0;
	$num = $db->num_rows($resql);

	while ($i < $num)
    {
		$row = $db->fetch_row($resql);

		print '<tr><td width=60>'.$row[0].'</td>';
		print '<td width=200 align="left">'.$row[1].'</td>';
		print '<td width=60 align="right">'.$row[2].'</td>';
		print '<td width=60 align="right">'.$row[3].'</td>';
		print '<td width=60 align="right">'.$row[4].'</td>';
		print '<td width=60 align="right">'.$row[5].'</td>';
		print '<td width=60 align="right">'.$row[6].'</td>';
		print '<td width=60 align="right">'.$row[7].'</td>';
		print '<td width=60 align="right">'.$row[8].'</td>';
		print '<td width=60 align="right">'.$row[9].'</td>';
		print '<td width=60 align="right">'.$row[10].'</td>';
		print '<td width=60 align="right">'.$row[11].'</td>';
		print '<td width=60 align="right">'.$row[12].'</td>';
		print '<td width=60 align="right">'.$row[13].'</td>';
		print '<td width=60 align="right"><b>'.$row[14].'</b></td>';
		print '</tr>';
      	$i++;
    }
	$db->free($resql);
}
else
{
	print $db->lasterror(); // affiche la derniere erreur sql
}

print "<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=60 align="left">'.$langs->trans("TotalVente").'</td>';
print '<td width=200 align="left">'.$langs->trans("Vide").'</td>';
print '<td width=60 align="center">'.$langs->trans("JanuaryMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("FebruaryMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("MarchMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("AprilMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("MayMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("JuneMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("JulyMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("AugustMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("SeptemberMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("OctoberMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("NovemberMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("DecemberMin").'</td>';
print '<td width=60 align="center"><b>'.$langs->trans("Total").'</b></td></tr>';


$sql = "SELECT '".$langs->trans("Vide")."' AS 'Total','".$langs->trans("Vide")."' AS 'Vide',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=1,fd.total_ht,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=2,fd.total_ht,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=3,fd.total_ht,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=4,fd.total_ht,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=5,fd.total_ht,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=6,fd.total_ht,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=7,fd.total_ht,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=8,fd.total_ht,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=9,fd.total_ht,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=10,fd.total_ht,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=11,fd.total_ht,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=12,fd.total_ht,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(fd.total_ht),2) as 'Total'";
$sql .= " FROM ".MAIN_DB_PREFIX."facturedet as fd";
$sql .= "  LEFT JOIN ".MAIN_DB_PREFIX."facture as f ON f.rowid = fd.fk_facture";
$sql .= " WHERE f.datef >= '".$db->idate(dol_get_first_day($y,1,false))."'";
$sql .= "  AND f.datef <= '".$db->idate(dol_get_last_day($y,12,false))."'";


$resql = $db->query($sql);
if ($resql)
{
	$i = 0;
	$num = $db->num_rows($resql);

	while ($i < $num)
    {
		$row = $db->fetch_row($resql);

		print '<tr><td width=60>'.$row[0].'</td>';
		print '<td width=200 align="left">'.$row[1].'</td>';
		print '<td width=60 align="right">'.$row[2].'</td>';
		print '<td width=60 align="right">'.$row[3].'</td>';
		print '<td width=60 align="right">'.$row[4].'</td>';
		print '<td width=60 align="right">'.$row[5].'</td>';
		print '<td width=60 align="right">'.$row[6].'</td>';
		print '<td width=60 align="right">'.$row[7].'</td>';
		print '<td width=60 align="right">'.$row[8].'</td>';
		print '<td width=60 align="right">'.$row[9].'</td>';
		print '<td width=60 align="right">'.$row[10].'</td>';
		print '<td width=60 align="right">'.$row[11].'</td>';
		print '<td width=60 align="right">'.$row[12].'</td>';
		print '<td width=60 align="right">'.$row[13].'</td>';
		print '<td width=60 align="right"><b>'.$row[14].'</b></td>';
		print '</tr>';
      	$i++;
    }
	$db->free($resql);
}
else
{
	print $db->lasterror(); // affiche la derniere erreur sql
}
print "</table>\n";

print "<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=60>'.$langs->trans("TotalMarge").'</td>';
print '<td width=200 align="left">'.$langs->trans("Vide").'</td>';
print '<td width=60 align="center">'.$langs->trans("JanuaryMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("FebruaryMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("MarchMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("AprilMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("MayMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("JuneMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("JulyMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("AugustMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("SeptemberMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("OctoberMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("NovemberMin").'</td>';
print '<td width=60 align="center">'.$langs->trans("DecemberMin").'</td>';
print '<td width=60 align="center"><b>'.$langs->trans("Total").'</b></td></tr>';


$sql = "SELECT '".$langs->trans("Vide")."' AS 'Marge','".$langs->trans("Vide")."' AS 'Vide',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=1,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=2,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=3,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=4,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=5,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=6,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=7,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=8,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=9,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=10,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=11,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(f.datef)=12,(fd.total_ht-(fd.qty * fd.buy_price_ht)),0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM((fd.total_ht-(fd.qty * fd.buy_price_ht))),2) as 'Total'";
$sql .= " FROM ".MAIN_DB_PREFIX."facturedet as fd";
$sql .= "  LEFT JOIN ".MAIN_DB_PREFIX."facture as f ON f.rowid = fd.fk_facture";
$sql .= " WHERE f.datef >= '".$db->idate(dol_get_first_day($y,1,false))."'";
$sql .= "  AND f.datef <= '".$db->idate(dol_get_last_day($y,12,false))."'";


$resql = $db->query($sql);
if ($resql)
{
	$i = 0;
	$num = $db->num_rows($resql);

	while ($i < $num)
    {
		$row = $db->fetch_row($resql);

		print '<tr><td width=60>'.$row[0].'</td>';
		print '<td width=200 align="left">'.$row[1].'</td>';
		print '<td width=60 align="right">'.$row[2].'</td>';
		print '<td width=60 align="right">'.$row[3].'</td>';
		print '<td width=60 align="right">'.$row[4].'</td>';
		print '<td width=60 align="right">'.$row[5].'</td>';
		print '<td width=60 align="right">'.$row[6].'</td>';
		print '<td width=60 align="right">'.$row[7].'</td>';
		print '<td width=60 align="right">'.$row[8].'</td>';
		print '<td width=60 align="right">'.$row[9].'</td>';
		print '<td width=60 align="right">'.$row[10].'</td>';
		print '<td width=60 align="right">'.$row[11].'</td>';
		print '<td width=60 align="right">'.$row[12].'</td>';
		print '<td width=60 align="right">'.$row[13].'</td>';
		print '<td width=60 align="right"><b>'.$row[14].'</b></td>';
		print '</tr>';
      	$i++;
    }
	$db->free($resql);
}
else
{
	print $db->lasterror(); // affiche la derniere erreur sql
}
print "</table>\n";
print "</table>\n";
print '</td></tr></table>';

llxFooter();
$db->close();

?>
