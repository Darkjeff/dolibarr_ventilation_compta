<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2014	   Ferran Marcet        <fmarcet@2byte.es>
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
 *	    \file       htdocs/compta/tva/index.php
 *      \ingroup    tax
 *		\brief      Index page of VAT reports
 */
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/tax.lib.php';
require_once DOL_DOCUMENT_ROOT.'/compta/tva/class/tva.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

$langs->load("other");
$langs->load("compta");
$langs->load("banks");
$langs->load("bills");

$year=GETPOST("year","int");
if ($year == 0)
{
    $year_current = strftime("%Y",time());
    $year_start = $year_current;
} else {
    $year_current = $year;
    $year_start = $year;
}

// Security check
$socid = isset($_GET["socid"])?$_GET["socid"]:'';
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'tax', '', '', 'charges');

// Define modetax (0 or 1)
// 0=normal, 1=option vat for services is on debit
$modetax = $conf->global->TAX_MODE;
if (isset($_GET["modetax"])) $modetax=$_GET["modetax"];


/**
 * print function
 *
 * @param 	DoliDB	$db		Database handler
 * @param 	string	$sql	SQL Request
 * @param 	string	$date	Date
 * @return	void
 */
function pt ($db, $sql, $date)
{
    global $conf, $bc,$langs;

    $result = $db->query($sql);
    if ($result)
    {
        $num = $db->num_rows($result);
        $i = 0;
        $total = 0;
        print '<table class="noborder" width="100%">';
        print '<tr class="liste_titre">';
        print '<td class="nowrap" width="60%">'.$date.'</td>';
        print '<td align="right">'.$langs->trans("Amount").'</td>';
        print '<td>&nbsp;</td>'."\n";
        print "</tr>\n";
        $var=True;
        while ($i < $num)
        {
            $obj = $db->fetch_object($result);
            $var=!$var;
            print '<tr '.$bc[$var].'>';
            print '<td class="nowrap">'.$obj->dm."</td>\n";
            $total = $total + $obj->mm;

            print '<td class="nowrap" align="right">'.price(price2num($obj->mm,1))."</td><td >&nbsp;</td>\n";
            print "</tr>\n";

            $i++;
        }
        print '<tr class="liste_total"><td align="right">'.$langs->trans("Total")." :</td><td class=\"nowrap\" align=\"right\"><b>".price(price2num($total,1))."</b></td><td>&nbsp;</td></tr>";

        print "</table>";
        $db->free($result);
    }
    else {
        dol_print_error($db);
    }
}



/*
 * View
 */

llxHeader();

$tva = new Tva($db);


$textprevyear="<a href=\"index.php?year=" . ($year_current-1) . "\">".img_previous($langs->trans("Previous"), 'class="valignbottom"')."</a>";
$textnextyear=" <a href=\"index.php?year=" . ($year_current+1) . "\">".img_next($langs->trans("Next"), 'class="valignbottom"')."</a>";

print $conf->dol_optimize_smallscreen;
print load_fiche_titre($langs->trans("VAT"), $textprevyear." ".$langs->trans("Year")." ".$year_start." ".$textnextyear, 'title_accountancy.png');

print $langs->trans("VATReportBuildWithOptionDefinedInModule").'<br>';
print '('.$langs->trans("TaxModuleSetupToModifyRules",DOL_URL_ROOT.'/admin/taxes.php').')<br>';
print '<br>';

print '<div class="fichecenter"><div class="fichethirdleft">';

print load_fiche_titre($langs->trans("VATSummary"), '', '');

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="30%">'.$langs->trans("Year")." ".$y.'</td>';
print '<td align="right">'.$langs->trans("VATToPay").'</td>';
print '<td align="right">'.$langs->trans("VATToCollect").'</td>';
print '<td align="right">'.$langs->trans("TotalToPay").'</td>';
print '<td>&nbsp;</td>'."\n";
print '</tr>'."\n";


$y = $year_current ;


$var=True;
$total=0; $subtotalcoll=0; $subtotalpaye=0; $subtotal=0;
$i=0;
for ($m = 1 ; $m < 13 ; $m++ )
{
    $coll_listsell = vat_by_date($db, $y, 0, 0, 0, $modetax, 'sell', $m);
    $coll_listbuy = vat_by_date($db, $y, 0, 0, 0, $modetax, 'buy', $m);
    
    $action = "tva";
    $object = array(&$coll_listsell, &$coll_listbuy);
    $parameters["mode"] = $modetax;
    $parameters["year"] = $y;
    $parameters["month"] = $m;
    $parameters["type"] = 'vat';
    
    // Initialize technical object to manage hooks of expenses. Note that conf->hooks_modules contains array array
    $hookmanager->initHooks(array('externalbalance'));
    $reshook=$hookmanager->executeHooks('addVatLine',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

    if (! is_array($coll_listbuy) && $coll_listbuy == -1)
    {
        $langs->load("errors");
        print '<tr><td colspan="5">'.$langs->trans("ErrorNoAccountancyModuleLoaded").'</td></tr>';
        break;
    }
    if (! is_array($coll_listbuy) && $coll_listbuy == -2)
    {
        print '<tr><td colspan="5">'.$langs->trans("FeatureNotYetAvailable").'</td></tr>';
        break;
    }

    $var=!$var;
    print "<tr ".$bc[$var].">";
    print '<td class="nowrap"><a href="quadri_detail.php?leftmenu=tax_vat&month='.$m.'&year='.$y.'">'.dol_print_date(dol_mktime(0,0,0,$m,1,$y),"%b %Y").'</a></td>';

    $x_coll = 0;
    foreach($coll_listsell as $vatrate=>$val)
    {
        $x_coll+=$val['vat'];
    }
    $subtotalcoll = $subtotalcoll + $x_coll;
    print "<td class=\"nowrap\" align=\"right\">".price(price2num($x_coll,1))."</td>";

    $x_paye = 0;
    foreach($coll_listbuy as $vatrate=>$val)
    {
        $x_paye+=$val['vat'];
    }
    $subtotalpaye = $subtotalpaye + $x_paye;
    print "<td class=\"nowrap\" align=\"right\">".price(price2num($x_paye,1))."</td>";

    $diff = $x_coll - $x_paye;
    $total = $total + $diff;
    $subtotal = $subtotal + $diff;

    print "<td class=\"nowrap\" align=\"right\">".price(price2num($diff,1))."</td>\n";
    print "<td>&nbsp;</td>\n";
    print "</tr>\n";

    $i++;
    if ($i > 2) {
        print '<tr class="liste_total">';
        print '<td align="right"><a href="quadri_detail.php?leftmenu=tax_vat&q='.($m/3).'&year='.$y.'">'.$langs->trans("SubTotal").'</a>:</td>';
        print '<td class="nowrap" align="right">'.price(price2num($subtotalcoll,1)).'</td>';
        print '<td class="nowrap" align="right">'.price(price2num($subtotalpaye,2)).'</td>';
        print '<td class="nowrap" align="right">'.price(price2num($subtotal,1)).'</td>';
        print '<td>&nbsp;</td></tr>';
        $i = 0;
        $subtotalcoll=0; $subtotalpaye=0; $subtotal=0;
    }
}
print '<tr class="liste_total"><td align="right" colspan="3">'.$langs->trans("TotalToPay").':</td><td class="nowrap" align="right">'.price(price2num($total,1)).'</td>';
print "<td>&nbsp;</td>\n";
print '</tr>';

print '</table>';


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';

print load_fiche_titre($langs->trans("VATPaid"), '', '');

/*
 * Payed
 */

$sql = "SELECT SUM(amount) as mm, date_format(f.datep,'%Y-%m') as dm";
$sql.= " FROM ".MAIN_DB_PREFIX."tva as f";
$sql.= " WHERE f.entity = ".$conf->entity;
$sql.= " AND f.datep >= '".$db->idate(dol_get_first_day($y,1,false))."'";
$sql.= " AND f.datep <= '".$db->idate(dol_get_last_day($y,12,false))."'";
$sql.= " GROUP BY dm ORDER BY dm ASC";

pt($db, $sql,$langs->trans("Year")." $y");


print load_fiche_titre($langs->trans("VATRecap"), '', '');
/*
 * Recap
 */

$sql1 = "SELECT SUM(amount) as mm, date_format(f.datev,'%Y') as dm";
$sql1.= " FROM ".MAIN_DB_PREFIX."tva as f";
$sql1.= " WHERE f.entity = ".$conf->entity;
$sql1.= " AND f.datev >= '".$db->idate(dol_get_first_day($y,1,false))."'";
$sql1.= " AND f.datev <= '".$db->idate(dol_get_last_day($y,12,false))."'";
$sql1.= " GROUP BY dm ORDER BY dm ASC";

 $result = $db->query($sql1);
    if ($result)
    {
	$obj = $db->fetch_object($result);
        print '<table class="noborder" width="100%">';
       		
			print "<tr>";
			print '<td align="right">'.$langs->trans("VATDue").'</td>';
			print '<td class="nowrap" align="right">'.price(price2num($total,1)).'</td>';
			print "</tr>\n";

			print "<tr>";
			print '<td align="right">'.$langs->trans("VATPayed").'</td>';
			print '<td class="nowrap" align="right">'.price(price2num($obj->mm,1))."</td><td >&nbsp;</td>\n";
            print "</tr>\n";
			
			
			
			$restopay = $total - $obj->mm ;
			print "<tr>";
			print '<td align="right">'.$langs->trans("VATRestopay").'</td>';
			print '<td class="nowrap" align="right">'.price(price2num($restopay,1)).'</td>';
			print "</tr>\n";
			
			print '</table>';
        
		}
print '</div>';
print '</div>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="60">' . $langs->trans("TurnoverbyVatrate") . '</td>';
print '<td width="60" align="left">' . $langs->trans("ProductOrService") . '</td>';
print '<td width="60" align="left">' . $langs->trans("Payed") . '</td>';
print '<td width="60" align="left">' . $langs->trans("Country") . '</td>';
for($i = 1; $i <= 12; $i ++) {
    print '<td width="60" align="right">' . $langs->trans('MonthShort' . str_pad($i, 2, '0', STR_PAD_LEFT)) . '</td>';
}
print '<td width="60" align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql = "SELECT " . $db->ifsql('fd.tva_tx IS NULL', "'".$langs->trans('NotMatch')."'", 'fd.tva_tx') . " AS vatrate,";
$sql .= "  " . $db->ifsql('fd.product_type IS NULL', "'".$langs->trans('NotMatch')."'", 'fd.product_type') . " AS product_type,";
$sql .= "  " . $db->ifsql('f.paye IS NULL', "'".$langs->trans('NotMatch')."'", 'f.paye') . " AS paye,";
$sql .= "  " . $db->ifsql('cc.label IS NULL', "'".$langs->trans('NotMatch')."'", 'cc.label') . " AS country,";
for($i = 4; $i <= 14; $i ++) {
	$sql .= "  SUM(" . $db->ifsql('MONTH(f.datef)=' . $i, 'fd.total_ht', '0') . ") AS month" . str_pad($i, 2, '0', STR_PAD_LEFT) . ",";
}
$sql .= "  SUM(fd.total_ht) as total";
$sql .= " FROM " . MAIN_DB_PREFIX . "facturedet as fd";
$sql .= "  LEFT JOIN " . MAIN_DB_PREFIX . "facture as f ON f.rowid = fd.fk_facture";
$sql .= "  INNER JOIN " . MAIN_DB_PREFIX . "societe as soc ON soc.rowid = f.fk_soc";
$sql .= "  INNER JOIN " . MAIN_DB_PREFIX . "c_country as cc ON cc.rowid = soc.fk_pays";
$sql .= " WHERE f.datef >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND f.datef <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " AND f.entity IN (" . getEntity("facture", 0) . ")"; 
$sql .= " GROUP BY fd.tva_tx,fd.product_type, f.paye, cc.label ";

dol_syslog("htdocs/compta/tva/index.php sql=" . $sql, LOG_DEBUG);
$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);

	while ( $row = $db->fetch_row($resql)) {

		$var = ! $var;
		print '<tr ' . $bc[$var] . '><td>' . vatrate($row[0]) . '</td>';
		if ($row[1] == 0) {
		//print '<td align="left">' . $row[1] . '</td>';
		print '<td align="left">'. $langs->trans("Product") . '</td>';
		} else {
		print '<td align="left">'. $langs->trans("Service") . '</td>';
		}
		if ($row[2] == 0) {
		//print '<td align="left">' . $row[1] . '</td>';
		print '<td align="left">'. $langs->trans("Payed") . '</td>';
		} else {
		print '<td align="left">'. $langs->trans("NotPayed") . '</td>';
		}
		print '<td align="left">' . $row[3] . '</td>';
		for($i = 4; $i <= 14; $i ++) {
			print '<td align="right">' . price($row[$i]) . '</td>';
		}
		print '<td align="right">' . price($row[14]) . '</td>';
		print '<td align="right"><b>' . price($row[15]) . '</b></td>';
		print '</tr>';
	}
	$db->free($resql);
} else {
	print $db->lasterror(); // Show last sql error
}
print "</table>\n";

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="200">' . $langs->trans("PurchasebyVatrate") . '</td>';
print '<td width="200" align="left">' . $langs->trans("ProductOrService") . '</td>';
for($i = 1; $i <= 12; $i ++) {
    print '<td width="60" align="right">' . $langs->trans('MonthShort' . str_pad($i, 2, '0', STR_PAD_LEFT)) . '</td>';
}
print '<td width="60" align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql2 = "SELECT  ".$db->ifsql('ffd.tva_tx IS NULL', "'".$langs->trans('NotMatch')."'", 'ffd.tva_tx') ." AS vatrate,";
$sql2 .= "  " . $db->ifsql('ffd.product_type IS NULL', "'".$langs->trans('NotMatch')."'", 'ffd.product_type') . " AS product_type,";
$sql2 .= "  " . $db->ifsql('ff.paye IS NULL', "'".$langs->trans('NotMatch')."'", 'ff.paye') . " AS paye,";
for($i = 1; $i <= 12; $i ++) {
	$sql2 .= "  SUM(" . $db->ifsql('MONTH(ff.datef)=' . $i, 'ffd.total_ht', '0') . ") AS month" . str_pad($i, 2, '0', STR_PAD_LEFT) . ",";
}
$sql2 .= "  ROUND(SUM(ffd.total_ht),2) as total";
$sql2 .= " FROM " . MAIN_DB_PREFIX . "facture_fourn_det as ffd";
$sql2 .= "  LEFT JOIN " . MAIN_DB_PREFIX . "facture_fourn as ff ON ff.rowid = ffd.fk_facture_fourn";
$sql2 .= " WHERE ff.datef >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql2 .= "  AND ff.datef <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql2 .= "  AND ff.fk_statut > 0 ";
$sql2 .= " AND ff.entity IN (" . getEntity("facture_fourn", 0) . ")";     
$sql2 .= " GROUP BY ffd.tva_tx,ffd.product_type";

dol_syslog("htdocs/compta/tva/index.php sql=" . $sql, LOG_DEBUG);
$resql2 = $db->query($sql2);
if ($resql2) {
	$num = $db->num_rows($resql2);

	while ( $row = $db->fetch_row($resql2)) {

		$var = ! $var;
		print '<tr ' . $bc[$var] . '><td>' . vatrate($row[0]) . '</td>';
		if ($row[1] == 0) {
		//print '<td align="left">' . $row[1] . '</td>';
		print '<td align="left">'. $langs->trans("Product") . '</td>';
		} else {
		print '<td align="left">'. $langs->trans("Service") . '</td>';
		}
		//print '<td align="left">' . $row[1] . '</td>';
		for($i = 2; $i <= 12; $i ++) {
			print '<td align="right">' . price($row[$i]) . '</td>';
		}
		print '<td align="right">' . price($row[13]) . '</td>';
		print '<td align="right"><b>' . price($row[14]) . '</b></td>';
		print '</tr>';
	}
	$db->free($resql2);
} else {
	print $db->lasterror(); // Show last sql error
}
print "</table>\n";


llxFooter();
$db->close();
