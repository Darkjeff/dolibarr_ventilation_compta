<?php
/* Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro   <alexandre.spangaro@gmail.com>
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
 */

/**
 *    \file       accountingex/admin/fiscalyear.php
 *		\ingroup    Accounting Expert
 *		\brief      Page to setup fiscal year for accountancy
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
dol_include_once("/core/lib/date.lib.php");
dol_include_once("/accountingex/class/fiscalyear.class.php");

$langs->load("companies");
$langs->load("admin");
$langs->load("compta");
$langs->load("accountingex@accountingex");

// Securite accès client
if ($user->societe_id > 0) accessforbidden();
if (!$user->rights->accountingex->admin) accessforbidden();

// List of statut
static $tmpstatut2label=array(
		'0'=>'OpenFiscalYear',
		'1'=>'CloseFiscalYear',
);
$statut2label=array('');
foreach ($tmpstatut2label as $key => $val) $statut2label[$key]=$langs->trans($val);

$mesg='';
$errors=array();
$action=GETPOST('action');

print $_POST['label'];
print $_POST['startmonth'];
print $_POST['startday'];
print $_POST['startyear'];
print $_POST['endmonth'];
print $_POST['endday'];
print $_POST['endyear'];

$object = new Accountancy($db);

/*
 * Actions
 */

// Add
if ($action == 'add')
{
    if (! GETPOST('cancel','alpha'))
    {
        $error=0;
        
        $object->label		  = GETPOST('label','alpha');
        $object->datestart	= dol_mktime(12, 0, 0, GETPOST('startmonth','int'), GETPOST('startday','int'), GETPOST('startyear','int'));
        $object->dateend  	= dol_mktime(12, 0, 0, GETPOST('endmonth','int'), GETPOST('endday','int'), GETPOST('endyear','int'));
        $object->statut     = 0;

        if (! $object->label)
        {
            $mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Label")).'</div>';
            $error++;
        }
        if (! $object->datestart)
        {
            $mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("DateStart")).'</div>';
            $error++;
        }
        if (! $object->dateend)
        {
            $mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("DateEnd")).'</div>';
            $error++;
        }

        if (! $error)
        {
            $id = $object->create();

            if ($id > 0)
            {
                header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $id);
                exit;
            }
            else
            {
                $mesg=$object->error;
                $action='create';
            }
        }
        else
        {
            $action='create';
        }
    }
    else
    {
        header("Location: index.php");
        exit;
    }
    
/*    
    if (! GETPOST('cancel','alpha'))
    {
        $error=0;
    
      	// Check values
      	$datestart = dol_mktime(12, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']);
        $dateend = dol_mktime(12, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']);
        $label = $_POST['label'];
            
          if (empty($label))
          {
              $mesgs[]='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->trans("Label")).'</div>';
              $error++;
              //$action='create';
          }
          if (empty($datestart) || empty($dateend))
          {
              $mesgs[]='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->trans("Date")).'</div>';
              $error++;
              //$action='create';
          }
    
    	    if (! $error)
    	    {
    	      $this->db->begin();
    	       
        		$sql = "INSERT INTO ".MAIN_DB_PREFIX."accountingfiscalyear";
        		$sql.= " (label, begin, end, statut, entity)";
        		$sql.= " VALUES('".$label."',";
        		$sql.= " '".$datebegin."',";
        		$sql.= " '".$dateend."',";
        		$sql.= " ' 0,";
        		$sql.= " ".$conf->entity."'";
            $sql.=')';
        
        		dol_syslog(get_class($this)."::create_label sql=".$sql);
        		if ($this->db->query($sql))
        		{
        			return 1;
        		}
        		else
        		{
        			$this->error=$this->db->lasterror();
        			$this->errno=$this->db->lasterrno();
        			return -1;
        		}
    	    }
    }
*/    	  
}

// Rename field
if ($action == 'update')
{
	if ($_POST["button"] != $langs->trans("Cancel"))
	{
        // Check values
		if (! GETPOST('type'))
		{
			$error++;
			$langs->load("errors");
			$mesg=$langs->trans("ErrorFieldRequired",$langs->trans("Type"));
			$action = 'create';
		}
		if (GETPOST('type')=='varchar' && $extrasize > $maxsizestring)
        {
            $error++;
            $langs->load("errors");
            $mesg=$langs->trans("ErrorSizeTooLongForVarcharType",$maxsizestring);
            $action = 'edit';
        }
        if (GETPOST('type')=='int' && $extrasize > $maxsizeint)
        {
            $error++;
            $langs->load("errors");
            $mesg=$langs->trans("ErrorSizeTooLongForIntType",$maxsizeint);
            $action = 'edit';
        }

	    if (! $error)
	    {
            if (isset($_POST["attrname"]) && preg_match("/^\w[a-zA-Z0-9-_]*$/",$_POST['attrname']))
    		{
    			$result=$extrafields->update($_POST['attrname'],$_POST['label'],$_POST['type'],$extrasize,$elementtype,(GETPOST('unique')?1:0));
    			if ($result > 0)
    			{
    				header("Location: ".$_SERVER["PHP_SELF"]);
    				exit;
    			}
    			else
    			{
                    $error++;
    			    $mesg=$extrafields->error;
    			}
    		}
    		else
    		{
    		    $error++;
    			$langs->load("errors");
    			$mesg=$langs->trans("ErrorFieldCanNotContainSpecialCharacters",$langs->transnoentities("AttributeCode"));
    		}
	    }
	}
}

// Delete attribute
if ($action == 'delete')
{
	if(isset($_GET["attrname"]) && preg_match("/^\w[a-zA-Z0-9-_]*$/",$_GET["attrname"]))
	{
        $result=$extrafields->delete($_GET["attrname"],$elementtype);
        if ($result >= 0)
        {
            header("Location: ".$_SERVER["PHP_SELF"]);
            exit;
        }
        else $mesg=$extrafields->error;
	}
	else
	{
	    $error++;
		$langs->load("errors");
		$mesg=$langs->trans("ErrorFieldCanNotContainSpecialCharacters",$langs->transnoentities("AttributeCode"));
	}
}

/*
 * View
 */

$form = new Form($db);

llxHeader('',$title);

$title = $langs->trans('Accountancysetup');
$tab = $langs->trans("Accountancy");

$linkback='';
print_fiche_titre($langs->trans('Fiscalyear'));

dol_htmloutput_errors($mesg);

// Load attribute_label
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Label").'</td>';
print '<td>'.$langs->trans("DateStart").'</td>';
print '<td>'.$langs->trans("DateEnd").'</td>';
print '<td align="right">'.$langs->trans("Statut").'</td>';
print '<td width="80">&nbsp;</td>';
print '</tr>';

/*$var=True;
foreach($extrafields->attribute_type as $key => $value)
{
    $var=!$var;
    print "<tr ".$bc[$var].">";
    print "<td>".$extrafields->attribute_label[$key]."</td>\n";
    print "<td>".$key."</td>\n";
    print "<td>".$type2label[$extrafields->attribute_type[$key]]."</td>\n";
    print '<td align="right">'.$extrafields->attribute_size[$key]."</td>\n";
    print '<td align="right">'.yn($extrafields->attribute_unique[$key])."</td>\n";
    print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=edit&attrname='.$key.'">'.img_edit().'</a>';
    print "&nbsp; <a href=\"".$_SERVER["PHP_SELF"]."?action=delete&attrname=$key\">".img_delete()."</a></td>\n";
    print "</tr>";
    //      $i++;
}
*/

print "</table>";

dol_fiche_end();


// Buttons
if ($action != 'add' && $action != 'edit')
{
    print '<div class="tabsAction">';
    print "<a class=\"butAction\" href=\"".$_SERVER["PHP_SELF"]."?action=create\">".$langs->trans("NewFiscalYear")."</a>";
    print "</div>";
}


/* ************************************************************************** */
/*                                                                            */
/* Create a fiscal year
/*                                                                            */
/* ************************************************************************** */

if ($action == 'create')
{
    $datetmp=dol_mktime(12,0,0,$_POST['startmonth'],$_POST['startday'],$_POST['startyear']);
    $datestart=($datetmp==''?(empty($conf->global->MAIN_AUTOFILL_DATE)?-1:0):$datetmp);
    $datetmp=dol_mktime(12,0,0,$_POST['endmonth'],$_POST['endday'],$_POST['endyear']);
    $dateend=($datetmp==''?-1:$datetmp);
        
    print "<br>";
    print_titre($langs->trans('NewFiscalYear'));

    print '<form name="add" action="'.$_SERVER["PHP_SELF"].'" method="post">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="add">';
    
    print '<table class="border" width="100%">';
    
    // Label
    print '<tr>';
    print '<td class="fieldrequired">'.$langs->trans("Label").'</td><td class="valeur"><input type="text" name="label" size="40"></td>';
    print '</tr>';
    // Date begin
    print '<tr><td class="fieldrequired">'.$langs->trans("DateStart").'</td><td colspan="2">';
    $form->select_date($datestart,'start','','','',"add",1,1);
    print '</td></tr>';
    // Date end
    print '<tr><td class="fieldrequired">'.$langs->trans("DateEnd").'</td><td colspan="2">';
    $form->select_date($dateend,'end','','','',"add",1,1);
    print '</td></tr>';
    // Statut
    print '<tr>';
    print '<td class="fieldrequired">'.$langs->trans("Statut").'</td><td class="valeur">'.$form->selectarray('statut',$statut2label,GETPOST('statut')).'</td>';
    print '</tr>';
    
    print '</table>';
    
    print '<div align="center"><br><input type="submit" name="button" class="button" value="'.$langs->trans("Save").'"> &nbsp; ';
    print '<input type="submit" name="button" class="button" value="'.$langs->trans("Cancel").'"></div>';
    
    print '</form>';
}

/* ************************************************************************** */
/*                                                                            */
/* Edition d'un champ optionnel                                               */
/*                                                                            */
/* ************************************************************************** */
if ($action == 'edit' && ! empty($attrname))
{
    print "<br>";
    print_titre($langs->trans("FieldEdition", $attrname));

    /*
     * formulaire d'edition
     */
    print '<form method="post" action="'.$_SERVER["PHP_SELF"].'?attrname='.$attrname.'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="attrname" value="'.$attrname.'">';
    print '<input type="hidden" name="action" value="update">';
    print '<table summary="listofattributes" class="border" width="100%">';

    // Label
    print '<tr>';
    print '<td class="fieldrequired" required>'.$langs->trans("Label").'</td><td class="valeur"><input type="text" name="label" size="40" value="'.$extrafields->attribute_label[$attrname].'"></td>';
    print '</tr>';
    // Code
    print '<tr>';
    print '<td class="fieldrequired" required>'.$langs->trans("AttributeCode").'</td>';
    print '<td class="valeur">'.$attrname.'&nbsp;</td>';
    print '</tr>';
    // Type
    $type=$extrafields->attribute_type[$attrname];
    $size=$extrafields->attribute_size[$attrname];
    print '<tr><td class="fieldrequired" required>'.$langs->trans("Type").'</td>';
    print '<td class="valeur">';
    print $type2label[$type];
    print '<input type="hidden" name="type" value="'.$type.'">';
    print '</td></tr>';
    // Size
    print '<tr><td class="fieldrequired" required>'.$langs->trans("Size").'</td><td class="valeur"><input type="text" name="size" size="5" value="'.$size.'"></td></tr>';

    print '</table>';

    print '<center><br><input type="submit" name="button" class="button" value="'.$langs->trans("Save").'"> &nbsp; ';
    print '<input type="submit" name="button" class="button" value="'.$langs->trans("Cancel").'"></center>';

    print "</form>";

}

llxFooter();

$db->close();
?>
