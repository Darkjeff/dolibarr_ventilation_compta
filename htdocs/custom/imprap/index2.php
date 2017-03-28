<?php
/* Copyright (C) 2017 saasprov <saasprov@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the SAASPROV General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * SAASPROV General Public License for more details.
 *
 * You should have received a copy of the SAASPROV General Public License
 * along with this program. If not, see <http://www.saasprov.ma/>.
 */

/**
 *       \file       htdocs/compta/bank/rappro.php
 *       \ingroup    banque
 *       \brief      Page to reconciliate bank transactions
 */

require('../../main.inc.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/bank.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/sociales/class/chargesociales.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/tva/class/tva.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/paiementfourn.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/cheque/class/remisecheque.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/bankcateg.class.php';

// start confuguration
include_once(DOL_DOCUMENT_ROOT.'/custom/imprap/class/repartition.class.php');

/*
 * View
 */

$form = new Form($db);

llxHeader();

$societestatic = new Societe($db);
$chargestatic = new ChargeSociales($db);
$paymentstatic = new Paiement($db);
$paymentsupplierstatic = new PaiementFourn($db);
$paymentvatstatic = new TVA($db);
$remisestatic = new RemiseCheque($db);

$now = dol_now();

	

	
	print '<table class="liste" width="100%">';
	print '<tr class="liste_titre">'."\n";
	print_liste_field_titre($langs->trans("DateOperationShort"),$_SERVER["PHP_SELF"],"b.dateo","",$paramlist,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("DateValueShort"),$_SERVER["PHP_SELF"],"b.datev","",$paramlist,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Type"),$_SERVER["PHP_SELF"],"b.fk_type","",$paramlist,'align="left"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Description"),$_SERVER["PHP_SELF"],"b.label","",$paramlist,'align="left"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Debit"),$_SERVER["PHP_SELF"],"b.amount","",$paramlist,' width="60 align="right"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Credit"),$_SERVER["PHP_SELF"],"b.amount","",$paramlist,' width="60 align="right"',$sortfield,$sortorder);
	print_liste_field_titre('',$_SERVER["PHP_SELF"],"","",$paramlist,' width="80 align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("ToConciliate"),$_SERVER["PHP_SELF"],"","",$paramlist,' align="center" width="80" ',$sortfield,$sortorder);
    print "</tr>\n";


			$var=!$var;
			print "<tr ".$bc[$var].">\n";
			
			print '<td align="center" class="nowrap">dddddddd</td>';

			// Date value
			
			print '<td align="center">Date value</td>';
			
			print '<td class="nowrap">num_chq</td>';

			// Description
			print '<td valign="center">link</td>';
			print "<td>&nbsp;</td><td align=\"right\" nowrap>price</td>\n";
			// If not already reconciliated
			print "<td align=\"center\">&nbsp;</td>";
				
			// Show checkbox for conciliation
			print '<td align="left">';
			print $langs->trans("FutureTransaction");
			print '</td>';

			print "</tr>\n";

			print "<tr ".$bc[$var].">\n";
			print "<td class='dateo'>colon1</td>";
			print "<td class='datev'>colon1</td>";
			print "<td width='25%'>colon1</td>";
			print "<td class='desct'>colon1</td>";
			print "<td class='debit'>colon1</td>";
			print "<td class='crdit'>colon1</td>";
			print "<td>--</td>";
			print "<td><span style='color:red;'>".$langs->trans('notSaved')."</span><br><input type='button' id='bbenregistrer' value='".$langs->trans('save')."'></td>";
			
			print "</tr>\n";
	
	
	
	
    print "</table><br>\n";
	

	
llxFooter();

$db->close();
