<?PHP
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013      Alexandre Spangaro   <alexandre.spangaro@gmail.com> 
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 */

/**
 *      \file       htdocs/accountingaccount/fiche.php
 *      \ingroup    Accounting Expert
 *      \brief      Page fiche accounting
 */
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
dol_include_once ( "/ventilation/class/accountingaccount.class.php");
dol_include_once ( "/ventilation/class/html.formventilation.class.php");

// langs
$langs->load("bills");
$langs->load("ventilation@ventilation");


$mesg = '';
$action = GETPOST('action');
$id = GETPOST('id','int');
$rowid = GETPOST('rowid','int');

// Securite acces client
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();

$accounting = new AccountingAccount($db);

//action
if (GETPOST ( "action" ) == 'add') {	
	$accounting->fk_pcg_version = GETPOST ( "fk_pcg_version" );
	$accounting->pcg_type = GETPOST ( "pcg_type" );
	$accounting->pcg_subtype = GETPOST ( "pcg_subtype" );
	$accounting->account_number = GETPOST ( "account_number" );
	$accounting->account_parent = GETPOST ( "account_parent" );
	$accounting->label = GETPOST ( "label" );
	$accounting->active = GETPOST ( "active" );
	
	
	$e_accounting = $accounting;
	
	$res = $accounting->create (  );
	if ($res == 0) {
	} else {
		if ($res == - 3) {
			$_error = 1;
			$action = "create";
		}
		if ($res == - 4) {
			$_error = 2;
			$action = "create";
		}
	}
	Header ( "Location: " . DOL_DOCUMENT_ROOT . "/ventilation/accountingaccount/liste.php" );
}
// Update record
else if ($action == 'edit')
{
    if (! GETPOST('cancel','alpha'))
    {
        $result = $accounting->fetch($id);

        $accounting->fk_pcg_version	= GETPOST('fk_pcg_version','int');
        $accounting->pcg_type 			= GETPOST('pcg_type','int');
        $accounting->pcg_subtype		= GETPOST('pcg_subtype','int');
        $accounting->account_number	= GETPOST('account_number','int');
        $accounting->account_parent	= GETPOST('account_parent','int');
        $accounting->label        	= GETPOST('label','alpha');
        $accounting->active         = GETPOST('active','int');

        $result = $accounting->update($user);

        if ($result > 0)
        {
            header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $id);
            exit;
        }
        else
        {
            $mesg=$object->error;
        }
    }
    else
    {
        header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $id);
        exit;
    }
}

/*
 * View
 *
 */
llxheader ( '', $langs->trans ( "Account" ), '' );
	 
$form = new Form($db);

if ($action == 'create') {

	print '<form name="add" action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
  print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
  print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">' . $langs->trans ( "AccountNumber" ) . '</td>';
	print '<td><input name="AccountNumber" size="30" value="' .$accounting->AccountNumber. '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "Label" ) . '</td>';
	print '<td><input name="Label" size="30" value="' .$accounting->Label. '"</td></tr>';
  print '<tr><td width="20%">' . $langs->trans ( "Accountparent" ) . '</td>';
	print '<td><input name="AccountParent" size="30" value="' .$accounting->AccountParent. '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "Pcgtype" ) . '</td>';
	print '<td><input name="pcgType" size="30" value="' .$accounting->pcgType. '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "Pcgsubtype" ) . '</td>';
	print '<td><input name="pcgSubType" size="30" value="' .$accounting->pcgSubType. '"</td></tr>';	
	print '<tr><td width="20%">' . $langs->trans ( "Active" ) . '</td>';
	print '<td><input name="Active" size="30" value="' .$accounting->Active. '"</td></tr>';	


	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
        
  print '</table>';
	print '</form>';
}
else if ($id)
{
  $rowid = $id;
  $account = $accounting->fetch($rowid);

  if ($account > 0)
  {	
     dol_htmloutput_mesg($mesg);

     if ($action == 'update')
     {
          //WYSIWYG Editor
          require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
 
          $soc = new Societe($db);
          if ($object->socid)
          {
               $soc->fetch($object->socid);
          }

	        print_fiche_titre($langs->trans("UpdateAccount"));
	
          print '<form name="update" action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
          print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
          print '<input type="hidden" name="action" value="edit">';
          print '<input type="hidden" name="id" value="'.$id.'">';
	
        	print '<table class="border" width="100%">';
        	
        	print '<tr><td width="20%">' . $langs->trans ( "AccountNumber" ) . '</td>';
        	print '<td><input name="AccountNumber" size="30" value="' .$accounting->account_number. '"</td></tr>';
        	print '<tr><td width="20%">' . $langs->trans ( "Label" ) . '</td>';
        	print '<td><input name="Label" size="30" value="' .$accounting->label. '"</td></tr>';
          print '<tr><td width="20%">' . $langs->trans ( "Accountparent" ) . '</td>';
        	print '<td><input name="AccountParent" size="30" value="' .$accounting->account_parent. '"</td></tr>';
        	print '<tr><td width="20%">' . $langs->trans ( "Pcgtype" ) . '</td>';
        	print '<td><input name="pcgType" size="30" value="' .$accounting->pcg_type. '"</td></tr>';
        	print '<tr><td width="20%">' . $langs->trans ( "Pcgsubtype" ) . '</td>';
        	print '<td><input name="pcgSubType" size="30" value="' .$accounting->pcg_subtype. '"</td></tr>';	
        	print '<tr><td width="20%">' . $langs->trans ( "Active" ) . '</td>';
        	print '<td><input name="Active" size="30" value="' .$accounting->active. '"</td></tr>';	
	
		      print '</table>';
	
	        print '<br><center><input type="submit" class="button" value="'.$langs->trans("Save").'"> &nbsp; ';
          print '<input type="submit" name="cancel" class="button" value="'.$langs->trans("Cancel").'">';
          print '</center>';

          print '</form>';

          print '</div>';
     } 
     else
     {
          print_fiche_titre($langs->trans("Account"));
	
          print '<table class="border" width="100%">';
        	
        	print '<tr><td width="20%">'.$langs->trans("AccountNumber").'</td>';
        	print '<td>'.$accounting->account_number.'</td></tr>';
        	print '<tr><td width="20%">'.$langs->trans("Label").'</td>';
        	print '<td>'.$accounting->label.'</td></tr>';
          print '<tr><td width="20%">'.$langs->trans("Accountparent").'</td>';
        	print '<td>'.$accounting->account_parent.'</td></tr>';
        	print '<tr><td width="20%">'.$langs->trans("Pcgtype").'</td>';
        	print '<td>'.$accounting->pcg_type.'</td></tr>';
        	print '<tr><td width="20%">'.$langs->trans("Pcgsubtype").'</td>';
        	print '<td>'.$accounting->pcg_subtype.'</td></tr>';	
        	print '<tr><td width="20%">'.$langs->trans("Active").'</td>';
        	print '<td>'.$accounting->active.'</td></tr>';	
	
		      print '</table>';

          print '</div>';
    }
  }
  else
  {
     dol_print_error($db);
  }
}

llxFooter();

$db->close();
?>