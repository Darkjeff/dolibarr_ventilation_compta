<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2013 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015      Alexandre Spangaro   <aspangaro.dolibarr@gmail.com>
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
 *     \file       htdocs/admin/taxes.php
 *     \ingroup    tax
 *     \brief      Page de configuration du module tax
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
if (! empty($conf->accounting->enabled)) require_once DOL_DOCUMENT_ROOT . '/accountancy/class/html.formventilation.class.php';

$langs->load('admin');

if (!$user->admin) accessforbidden();

$action = GETPOST('action','alpha');

// Other parameters ACCOUNTING_*
$list = array (
		'ACCOUNTING_VAT_PAY_ACCOUNT'
// MODIF CCA - TVA à l'encaissement sur produit
		, 'TAX_CASHING_PROD_PAYMENT'
// Fin Modif CCA	
);

/*
 * Actions
 */

// 0=normal, 1=option vat for services is on debit

// TAX_MODE=0 (most cases):
//              Buy                     Sell
// Product      On delivery             On delivery
// Service      On payment              On payment

// TAX_MODE=1 (option):
//              Buy                     Sell
// Product      On delivery             On delivery
// Service      On invoice              On invoice

//
// MODIF CCA - TVA à l'encaissement sur produit
// TAX_CASHING_PROD_PAYMENT  = 	"RAS" si TAX_MODE=1
// TAX_CASHING_PROD_PAYMENT  = 	"PRODPAYMENT" si TAX_MODE=0 and TVA sur Produit exigible à l'encaissement
// TAX_CASHING_PROD_PAYMENT  = 	"PRODDEBIT" si TAX_MODE=0 and TVA sur Produit exigible à la facturation (cas standard FRANCE)
// Fin MODIF CCA

$tax_mode = empty($conf->global->TAX_MODE)?0:$conf->global->TAX_MODE;
// MODIF CCA - TVA à l'encaissement sur produit
if ($tax_mode) $tax_cashing_prod_payment = "RAS";
else  $tax_cashing_prod_payment = empty($conf->global->TAX_CASHING_PROD_PAYMENT)?"RAS":$conf->global->TAX_CASHING_PROD_PAYMENT;
// Fin Modif CCA	
if ($action == 'update') {
    $error = 0;

	// Tax mode
	$tax_mode = GETPOST('tax_mode','alpha');
// MODIF CCA - TVA à l'encaissement sur produit
	$tax_cashing_prod_payment = GETPOST('TAX_CASHING_PROD_PAYMENT','alpha');
// Fin Modif CCA	

    $db->begin();

    $res = dolibarr_set_const($db, 'TAX_MODE', $tax_mode,'chaine',0,'',$conf->entity);
    if (! $res > 0) $error++;

    switch ($tax_mode)
    {
        case 0:
            $value = 'payment';
            break;
        case 1:
            $value = 'invoice';
            break;
    }
// MODIF CCA - TVA à l'encaissement sur produit
	$valueProd =  'invoice';
	if ($tax_mode == 0 and $tax_cashing_prod_payment == 0) $valueProd = 'payment';
// Fin Modif CCA	

    $res = dolibarr_set_const($db, 'TAX_MODE_SELL_PRODUCT', 'invoice','chaine',0,'',$conf->entity);
    if (! $res > 0) $error++;
    $res = dolibarr_set_const($db, 'TAX_MODE_BUY_PRODUCT', 'invoice','chaine',0,'',$conf->entity);
    if (! $res > 0) $error++;
    $res = dolibarr_set_const($db, 'TAX_MODE_SELL_SERVICE', $value,'chaine',0,'',$conf->entity);
    if (! $res > 0) $error++;
    $res = dolibarr_set_const($db, 'TAX_MODE_BUY_SERVICE', $value,'chaine',0,'',$conf->entity);
    if (! $res > 0) $error++;
	

	// Others options
    foreach ($list as $constname) {
        $constvalue = GETPOST($constname, 'alpha');

        if (!dolibarr_set_const($db, $constname, $constvalue, 'chaine', 0, '', $conf->entity)) {
            $error++;
        }
    }

    if (! $error) {
        $db->commit();
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        $db->rollback();
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}


/*
 * View
 */
llxHeader();
$form=new Form($db);
if (! empty($conf->accounting->enabled)) $formaccountancy = New FormVentilation($db);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans('TaxSetup'),$linkback,'title_setup');

dol_fiche_head();

if (empty($mysoc->tva_assuj))
{
    print $langs->trans("YourCompanyDoesNotUseVAT").'<br>';
}
else
{
    print '<table class="noborder" width="100%">';

    // Cas des parametres TAX_MODE_SELL/BUY_SERVICE/PRODUCT
    print '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="update">';

    print '<tr class="liste_titre">';
    print '<td colspan="2">'.$langs->trans('OptionVatMode').'</td><td>'.$langs->trans('Description').'</td>';
    print "</tr>\n";
// MODIF CCA - TVA à l'encaissement sur produit
	print PrepJavascript();
    print '<tr '.$bc[false].'><td width="200"><input type="radio" name="tax_mode" value="0"'.($tax_mode != 1 ? ' checked' : '').' onclick="ModifAff(this)" id="tax_mode_std"> '.$langs->trans('OptionVATDefault').'</td>';
    print '<td colspan="2">'.nl2br($langs->trans('OptionVatDefaultDesc'));
    print "</td></tr>\n";
    print '<tr '.$bc[true].'><td width="200"><input type="radio" name="tax_mode" value="1"'.($tax_mode == 1 ? ' checked' : '').' onclick="ModifAff(this)" id="tax_mode_debit"> '.$langs->trans('OptionVATDebitOption').'</td>';
// Fin Modif CCA 
 print '<td colspan="2">'.nl2br($langs->trans('OptionVatDebitOptionDesc'))."</td></tr>\n";


// MODIF CCA - TVA à l'encaissement sur produit
	// Param
	$label = $langs->trans($key); 
	print '<tr><td colspan=2><label for="TAX_CASHING_PROD_PAYMENT">'.$langs->trans('TAX_CASHING_PROD_PAYMENT').'</label></td>';

	// Value
	print '<td>';
	print select_mod_tva($tax_cashing_prod_payment, 'TAX_CASHING_PROD_PAYMENT', $tax_mode);
	
	print "</td></tr>\n";
 // Fin Modif CCA
 print "</table>\n";
			
    print '<br>';
    print load_fiche_titre($langs->trans("SummaryOfVatExigibilityUsedByDefault"),'','');
    //print ' ('.$langs->trans("CanBeChangedWhenMakingInvoice").')';

    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td>&nbsp;</td><td>'.$langs->trans("Buy").'</td><td>'.$langs->trans("Sell").'</td></tr>';

    // Products
    print '<tr '.$bc[false].'><td>'.$langs->trans("Product").'</td>';
    print '<td>';
	
// MODIF CCA - TVA à l'encaissement sur produit
    print '<span id="Tax_mod_libProdBuy">';
	if ($tax_mode == 0 and $tax_cashing_prod_payment == 'PRODPAYMENT')
    {
        print $langs->trans("OnPayment");
        print ' ('.$langs->trans("SupposedToBePaymentDate").')';
	}
	else {
		print $langs->trans("OnDelivery");
		print ' ('.$langs->trans("SupposedToBeInvoiceDate").')';
	}
    print '</span></td>';
    print '<td>';
    print '<span id="Tax_mod_libProdSell">';
    if ($tax_mode == 0 and $tax_cashing_prod_payment == 1)
    {
        print $langs->trans("OnPayment");
        print ' ('.$langs->trans("SupposedToBePaymentDate").')';
	}
	else {
		print $langs->trans("OnDelivery");
		print ' ('.$langs->trans("SupposedToBeInvoiceDate").')';
	}
    print '</span></td></tr>';

    // Services
    print '<tr '.$bc[true].'><td>'.$langs->trans("Services").'</td>';
    print '<td>';
    print '<span id="Tax_mod_libServBuy">';
    if ($tax_mode == 0)
    {
        print $langs->trans("OnPayment");
        print ' ('.$langs->trans("SupposedToBePaymentDate").')';
    }
    if ($tax_mode == 1)
    {
        print $langs->trans("OnInvoice");
        print ' ('.$langs->trans("InvoiceDateUsed").')';
    }
    print '</span></td>';
    print '<td>';
    print '<span id="Tax_mod_libServSell">';
    if ($tax_mode == 0)
    {
        print $langs->trans("OnPayment");
        print ' ('.$langs->trans("SupposedToBePaymentDate").')';
    }
    if ($tax_mode == 1)
    {
        print $langs->trans("OnInvoice");
        print ' ('.$langs->trans("InvoiceDateUsed").')';
    }
    print '</span></td></tr>';
 // Fin Modif CCA

    print '</table>';
}

print "<br>\n";

/*
 *  Others params
 */
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td colspan="3">' . $langs->trans('OtherOptions') . '</td>';
print "</tr>\n";

foreach ($list as $key)
{
	$var=!$var;	
		
// MODIF CCA - TVA à l'encaissement sur produit
	print '<tr '.$bc[$var].' class="value">';
	switch ($key)
    {
        case 'ACCOUNTING_VAT_PAY_ACCOUNT': 
			// Param
			$label = $langs->trans($key); 
			print '<td><label for="'.$key.'">'.$label.'</label></td>';

			// Value
			print '<td>';
			if (! empty($conf->accounting->enabled))
			{
				print $formaccountancy->select_account($conf->global->$key, $key, 1, '', 1, 1);
			}
			else
			{
				print '<input type="text" size="20" id="'.$key.'" name="'.$key.'" value="'.$conf->global->$key.'">';
			}
			print '</td>';
			break;
        case 'TAX_CASHING_PROD_PAYMENT': 
			break;
	}
	print '</tr>';
// Fin MODIF CCA 
}

print '</table>';

dol_fiche_end();

print '<div class="center">';
print '<input type="submit" class="button" value="' . $langs->trans("Modify") . '" name="button">';
print '</div>';

print '</form>';

llxFooter();
$db->close();

// MODIF CCA - TVA à l'encaissement sur produit
function select_mod_tva($selected, $htmlname, $tax_mode)
{	
	global $langs;
	
        $out.= '<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'" onchange="ModifAff(this)" >';
		if ($tax_mode == 1)        $out.= '<option value="RAS" selected="selected">'.$langs->trans('TAX_CASHING_CHOIIX0').'</option>';
		else       {
				if ($selected == 'PRODPAYMENT') $out.= '<option value="PRODPAYMENT" selected="selected">'.$langs->trans('TAX_CASHING_CHOIIX1').'</option>';
				else 				$out.= '<option value="PRODPAYMENT">'.$langs->trans('TAX_CASHING_CHOIIX1').'</option>';
				if ($selected == 'PRODDEBIT') $out.= '<option value="PRODDEBIT" selected="selected">'.$langs->trans('TAX_CASHING_CHOIIX2').'</option>';
				else 				$out.= '<option value="PRODDEBIT">'.$langs->trans('TAX_CASHING_CHOIIX2').'</option>';
		}
        $out.= '</select>';
   
		return $out;
	
} //select_mod_tva
///* document.getElementById("Tax_mod_libProdBuy").innerHTML='.$langs->trans("OnDelivery").'('.$langs->trans("SupposedToBeInvoiceDate").');	*/
function PrepJavascript()
{
	global $langs;
	$out = '';
	$out .= '<script>
			function ModifAff(o) {
				var select = document.getElementById ("TAX_CASHING_PROD_PAYMENT");

				selected =  select.selectedIndex;
				selectvalue= select.value;
				if (o.id == "tax_mode_std" || o.id == "TAX_CASHING_PROD_PAYMENT") {
					 tax_cashing_prof_invoice = document.getElementById("TAX_CASHING_PROD_PAYMENT").value;
					if (tax_cashing_prof_invoice == "PRODPAYMENT") {
						libProd="'.$langs->trans("OnPayment").' ('.$langs->trans("SupposedToBePaymentDate").')";	
						libServ="'.$langs->trans("OnPayment").' ('.$langs->trans("SupposedToBePaymentDate").')";
						select.options.remove (0);	
						select.options.remove (1);	
						var newOption = new Option ("'.$langs->trans('TAX_CASHING_CHOIIX1').'", "PRODPAYMENT");
						select.options.add (newOption);	
						var newOption = new Option ("'.$langs->trans('TAX_CASHING_CHOIIX2').'", "PRODDEBIT");
						select.options.add (newOption);	
						if (o.id == "TAX_CASHING_PROD_PAYMENT")  select.selectedIndex = selected;
					}
					else {
						libProd="'.$langs->trans("OnDelivery").' ('.$langs->trans("SupposedToBeInvoiceDate").')";
						libServ="'.$langs->trans("OnPayment").' ('.$langs->trans("SupposedToBePaymentDate").')";
						select.options.remove (0);	
						select.options.remove (1);	
						var newOption = new Option ("'.$langs->trans('TAX_CASHING_CHOIIX1').'", "PRODPAYMENT");
						select.options.add (newOption);	
						var newOption = new Option ("'.$langs->trans('TAX_CASHING_CHOIIX2').'", "PRODDEBIT");
						select.options.add (newOption);			
						if (o.id == "TAX_CASHING_PROD_PAYMENT")  select.selectedIndex = selected;
					};
					if (selectvalue == "RAS") select.selectedIndex =  1;
				 }
				 if (o.id == "tax_mode_debit" ) {
					libProd="'.$langs->trans("OnDelivery").' ('.$langs->trans("SupposedToBeInvoiceDate").')";
					libServ="'.$langs->trans("OnDelivery").' ('.$langs->trans("SupposedToBeInvoiceDate").')";	
					select.options.remove (0);	
					select.options.remove (1);	
					var newOption = new Option ("'.$langs->trans('TAX_CASHING_CHOIIX0').'", "RAS");
					select.options.add (newOption);	
				 };
				document.getElementById("Tax_mod_libProdBuy").innerHTML=libProd;
				document.getElementById("Tax_mod_libProdSell").innerHTML=libProd;
				document.getElementById("Tax_mod_libServBuy").innerHTML=libServ;
				document.getElementById("Tax_mod_libServSell").innerHTML=libServ;
				 /*console.log(o.id);*/
			}
			</script>';
	return $out;
} //PrepJavascript
// Fin Mod CCA
