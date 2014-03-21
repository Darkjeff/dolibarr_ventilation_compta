<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro   <alexandre.spangaro@fidurex.fr>  
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
 *	\file       accountingex/supplier/liste.php
 *	\ingroup    Accounting Expert
 *	\brief      Page de ventilation des lignes de facture
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
dol_include_once("/fourn/class/fournisseur.facture.class.php");
dol_include_once("/fourn/class/fournisseur.product.class.php");

// Langs
$langs->load("compta");
$langs->load("bills");
$langs->load("other");
$langs->load("main");
$langs->load("accountingex@accountingex");


// Security check
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->access) accessforbidden();

/*
 * View
 */
 
llxHeader('',$langs->trans("Ventilation"));

if($_POST["action"] == 'ventil')
  {
  print '<div><font color="red">Debut du traitement... </font></div>';
  if($_POST['codeventil'] && $_POST["mesCasesCochees"])
  {
    print '<div><font color="red">'.count($_POST["mesCasesCochees"])." Lignes selectionnees</font></div>";
    $mesLignesCochees=$_POST['mesCasesCochees'];
    $mesCodesVentilChoisis = $_POST['codeventil'];
    $cpt = 0;
    foreach($mesLignesCochees as $maLigneCochee) 
      {
      //print '<div><font color="red">id selectionnee : '.$monChoix."</font></div>";
      $maLigneCourante = split("_", $maLigneCochee);
      $monId = $maLigneCourante[0];
      $monNumLigne = $maLigneCourante[1];
      $monCompte = $mesCodesVentilChoisis[$monNumLigne];
  
      $sql = " UPDATE ".MAIN_DB_PREFIX."facture_fourn_det";
      $sql .= " SET fk_code_ventilation = ".$monCompte;
      $sql .= " WHERE rowid = ".$monId;

      if($db->query($sql))
      {
            print '<div><font color="green">'.$langs->trans("Lineofinvoice").' '.$monId.' '.$langs->trans("VentilatedinAccount").' : '.$monCompte.'</font></div>';
      }
      else 
      {
           print '<div><font color="red">'.$langs->trans("ErrorDB").' : '.$langs->trans("Lineofinvoice").' '.$monId.' '.$langs->trans("NotVentilatedinAccount").' : '.$monCompte.'<br/> <pre>'.$sql.'</pre></font></div>';
      }
  
      $cpt++; 
  
      }
    }
    else
    {
      print '<div><font color="red">'.$langs->trans("AnyLineVentilate").'</font></div>';
    }
    print '<div><font color="red">'.$langs->trans("EndProcessing").'</font></div>';
  }

/* 
 * Liste des comptes
 */

$sqlCompte = "SELECT a.rowid, a.account_number, a.label, a.fk_pcg_version";
$sqlCompte .= " , s.rowid, s.pcg_version";
$sqlCompte .= " FROM ".MAIN_DB_PREFIX."accountingaccount as a, ".MAIN_DB_PREFIX."accounting_system as s";
$sqlCompte .= " WHERE a.fk_pcg_version = s.pcg_version AND s.rowid=".$conf->global->CHARTOFACCOUNTS;
$sqlCompte .= " AND a.active = '1'";
$sqlCompte .= " ORDER BY a.account_number ASC";

$resultCompte = $db->query($sqlCompte);
$cgs = array();
$cgn = array();
if ($resultCompte)
{
  $numCompte = $db->num_rows($resultCompte);
  $iCompte = 0; 
  
  while ($iCompte < $numCompte)
    {
      $rowCompte = $db->fetch_row($resultCompte);
      $cgs[$rowCompte[0]] = $rowCompte[1] . ' ' . $rowCompte[2];
      $cgn[$rowCompte[1]] = $rowCompte[0];
      $iCompte++;
    }
}



/*
 * Lignes de factures
 *
 */
$page = $_GET["page"];
if ($page < 0) $page = 0;
$limit = $conf->global->LIMIT_LIST_VENTILATION;
$offset = $limit * $page ;

$sql = "SELECT f.ref, f.rowid as facid, f.ref_supplier, l.fk_product, l.description, l.total_ht as price, l.rowid, l.fk_code_ventilation, ";
$sql.= " p.rowid as product_id, p.ref as product_ref, p.label as product_label, p.fk_product_type as type, p.accountancy_code_buy as code_buy";
$sql.= " FROM ".MAIN_DB_PREFIX."facture_fourn as f";
$sql.= " , ".MAIN_DB_PREFIX."facture_fourn_det as l";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product as p ON p.rowid = l.fk_product";
$sql.= " WHERE f.rowid = l.fk_facture_fourn AND f.fk_statut > 0 AND fk_code_ventilation = 0";
$sql.= " ORDER BY l.rowid";
if (! empty($conf->global->LIST_SORT_VENTILATION)) { $sql.= " DESC "; }
$sql.= $db->plimit($limit+1,$offset);

$result = $db->query($sql);
if ($result)
{
  $num_lignes = $db->num_rows($result);
  $i = 0; 
  
  print_barre_liste("Lignes de facture fournisseurs à ventiler",$page,"liste.php","",$sortfield,$sortorder,'',$num_lignes);

  print '<td align="left"><br><b>'.$langs->trans("DescVentilTodoSupplier").'</b></br></td>';

	print '<form action="liste.php" method="post">'."\n";
	print '<input type="hidden" name="action" value="ventil">';

  print '<table class="noborder" width="100%">';
  print '<tr class="liste_titre"><td>Facture</td>';
  print '<td align="left">'.$langs->trans("Ref").'</td>';
  print '<td align="left">'.$langs->trans("Label").'</td>';
  print '<td>'.$langs->trans("Description").'</td>';
  print '<td align="right">'.$langs->trans("Amount").'</td>';
  print '<td align="right">'.$langs->trans("Compte").'</td>';
  print '<td align="center">'.$langs->trans("IntoAccount").'</td>';
  print '<td align="center">'.$langs->trans("Ventilate").'</td>';
  print "</tr>\n";
  
  $facturefourn_static=new FactureFournisseur($db);
	$productfourn_static=new ProductFournisseur($db);
	$form = new Form($db);           

  $var=True;
  while ($i < min($num_lignes, $limit))
    {
      $objp = $db->fetch_object($result);
      $var=!$var;
      print "<tr $bc[$var]>";
      
      // Ref facture
  		$facturefourn_static->ref=$objp->ref;
  		$facturefourn_static->id=$objp->facid;
  		print '<td>'.$facturefourn_static->getNomUrl(1).'</td>';

  		// Ref facture supplier
  		$productfourn_static->ref=$objp->product_ref;
  		$productfourn_static->id=$objp->product_id;
  		$productfourn_static->type=$objp->type;
  		print '<td>';
  		if ($productfourn_static->id) print $productfourn_static->getNomUrl(1);
  		else print '&nbsp;';
  		print '</td>';
    
      // print '<td><a href="'.DOL_URL_ROOT.'/fourn/facture/fiche.php?facid='.$objp->facid.'">'.$objp->ref.'</a></td>';

      // print '<td><a href="'.DOL_URL_ROOT.'/fourn/facture/fiche.php?facid='.$objp->facid.'">'.$objp->ref_supplier.'</a></td>';
      
      print '<td>'.dol_trunc($objp->product_label,24).'</td>';

      print '<td>'.stripslashes(nl2br($objp->description)).'</td>';                       

      print '<td align="right">';
      print price($objp->price);
      print '</td>';
      
      print '<td align="right">';
      print $objp->code_buy;
      print '</td>';

  		//Colonne choix du compte
  		print '<td align="center">';
  		print $form->selectarray("codeventil[]",$cgs, $cgn[$objp->code_buy]);
  		print '</td>';
  		//Colonne choix ligne a ventiler
  		print '<td align="center">';
  		print '<input type="checkbox" name="mesCasesCochees[]" value="'.$objp->rowid."_".$i.'"'.($objp->code_buy?"checked":"").'/>';
  		print '</td>';

      print "</tr>";
      $i++;
    }

print '<tr><td colspan="8">&nbsp;</td></tr><tr><td colspan="8" align="center"><input type="submit" class="butAction" value="'.$langs->trans("Ventiler").'"></td></tr>';
  
print "</table>";

	print '</form>';
}
else
{
  print $db->error();
}
$db->close();

llxFooter();
?>
