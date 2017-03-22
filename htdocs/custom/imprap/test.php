<?php

require('../../main.inc.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/bank.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/sociales/class/chargesociales.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/tva/class/tva.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/paiementfourn.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/cheque/class/remisecheque.class.php';

// start confuguration
// Change this following line to use the correct relative path from htdocs
include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
dol_include_once('/mymodule/class/skeleton_class.class.php');
include_once(DOL_DOCUMENT_ROOT.'/custom/imprap/class/repartition.class.php');
// Load traductions files requiredby by page
$langs->load("mymodule");
$langs->load("other");
// end confuguration

$langs->load("banks");
$langs->load("categories");
$langs->load("bills");

print'
<script>
$("#selectboxid").change(function() {
  $.ajax({ url: "test.html", context: document.body, success: function(){
    $(this).addClass("done");
  }});
}); 
</script>
';