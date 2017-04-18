<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <2017> SaaSprov <saasprov@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \defgroup    imprap    ImpRap module
 * \brief       ImpRap module descriptor.
 *
 * Put detailed description here.
 */

/**
 * \file        core/modules/modImpRap.class.php
 * \ingroup     imprap
 * \brief       Example module description and activation file.
 *
 * Put detailed description here.
 */
 
if (false === (@include '../main.inc.php')) {  // From htdocs directory
	require '../../main.inc.php'; // From "custom" directory
}

include_once(DOL_DOCUMENT_ROOT.'/custom/imprap/lib/bankrapprofile.lib.php');
include_once(DOL_DOCUMENT_ROOT.'/custom/imprap/class/repartition.class.php');

$form = new Form($db);

$action = GETPOST('action', 'alpha');

$accountid = GETPOST('accountid');
$separateur = GETPOST('separateur');
$entete = GETPOST('entete');

if($action == 'insert'){
	
	$error = 0;

	// Testons si l'extension est autorisie
	$infosfichier = pathinfo($_FILES['fcsv']['name']);
	$extension_upload = $infosfichier['extension'];
	$extensions_autorisees = array('csv');
	
	if (in_array($extension_upload, $extensions_autorisees))
	{
		
		$lines = file($_FILES['fcsv']['tmp_name'], FILE_IGNORE_NEW_LINES);
		
		if(!empty($entete)){
			unset($lines[0]);
		}
		
		if(empty($accountid)){
			$error++;
			$langs->load("errors");
			setEventMessages($langs->trans("ErrorAccount"), null, 'errors');
		}
		
		if(!empty(trim($separateur))){
			if(strlen($separateur) > 1){
				
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("ErrorSeparateur"), null, 'errors');
				
			}else{
				$separateur = trim($separateur);
			}
		}else{	$separateur = ';'; }
		
		
		foreach ($lines as $key => $value)
		{			
			$cell = explode(trim($separateur),$value);
			
			
			if ( (!empty($cell[0]) AND preg_match( "/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $cell[0] )) OR (!empty($cell[0]) AND preg_match( "/^[0-9]{4}[\/-](0[1-9]|1[0-2])[\/-](0[1-9]|[1-2][0-9]|3[0-1])$/", $cell[0] )) ){
				
				$originalDate = str_replace("/","-",$cell[0]);
				$cell[0] = date("Y-m-d", strtotime($originalDate));
			
			}else{ 				
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("dateOperationInvalid"), null, 'errors');
				break;
			}
			
			if ( (!empty($cell[1]) AND preg_match( "/^(0[1-9]|[1-2][0-9]|3[0-1])[\/-](0[1-9]|1[0-2])[\/-][0-9]{4}$/", $cell[1] )) OR (!empty($cell[1]) AND preg_match( "/^[0-9]{4}[\/-](0[1-9]|1[0-2])[\/-](0[1-9]|[1-2][0-9]|3[0-1])$/", $cell[1] )) ){
				
				$originalDate = str_replace("/","-",$cell[1]);
				$cell[1] = date("Y-m-d", strtotime($originalDate));
			
			}else{ 				
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("dateValeurInvalid"), null, 'errors');
				break;
			}
			
			$cell[2] = (float)(trim(str_replace(",",".",$cell[2])));
			$cell[3] = (float)(trim(str_replace(",",".",$cell[3])));
			$cell[5] = (float)(trim(str_replace(",",".",$cell[5])));
			
			if(! is_float($cell[2])){
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("debutError"), null, 'errors');
				break;				
			}
			
			if(! is_float($cell[3])){
				$error++;
				$langs->load("errors");
				setEventMessages($langs->trans("creditError"), null, 'errors');
				break;				
			}
			
			$operation = new repartition($db);
			
			$operation->date_d_operation = $cell[0] ;
			$operation->date_de_valeur = $cell[1] ;
			$operation->debut = $cell[2] ;
			$operation->credit = $cell[3] ;
			$operation->libelle = $cell[4] ;
			$operation->solde = $cell[5] ;
			$operation->accountid = $accountid ;
			
			$test = true ;
			
			$sql = "SELECT * FROM ". MAIN_DB_PREFIX . "bank_rapprofile";
			$resqlv = $db->query($sql);
			if ($resqlv)
			{
				$num = $db->num_rows($resqlv);
				$i = 0;

				if ($num)
				{
					while ($i < $num)
					{
						$objv = $db->fetch_object($resqlv);
						if($operation->date_d_operation == $objv->date_d_operation AND $operation->date_de_valeur == $objv->date_de_valeur AND $operation->libelle == $objv->libelle AND $operation->debut == $objv->debut AND $operation->credit == $objv->credit AND $operation->accountid == $objv->fk_account ){
							$test = false ;
						}
						$i++;
					}
				}
			}
			
			if($test){	
				$operation->createOperation();
			}
		}
		if($error == 0){
			setEventMessages($langs->trans("bienAjouter"),null);
		}
		
		
	}else{
		$error++;
		$langs->load("errors");
		setEventMessages($langs->trans("extentionInvalid"), null, 'errors');
		
	}
	
	
}


llxHeader();

// Onglets
$head = bank_rappro_file();
dol_fiche_head($head, 'import', $langs->trans("rappro"), 0 ,'account');


print '<form method="POST" action="index.php?action=insert" enctype="multipart/form-data">';
print '<input type="hidden" name="newFile" value="1">';
	dol_fiche_head();
	print load_fiche_titre($langs->trans("Import fichier CSV"));
	
		print ' <table id="tb1" class="liste" width="100%">
			  <tr>
				<td>Fichier à importer :</td><td><input class="flat" type="file" size="33" name="fcsv"/></td>
				
				<td>Séparateur des données du fichier : </td>
				<td><input type="text" name="separateur" size="1" value="'.$separateur.'"/></td>
			  </tr>
			  
			  <tr>';
			  print '<td>'. $langs->trans('CompteBank') .'</td>';
			  print '<td>';
					$form->select_comptes(	 	
						$selected = $accountid,
						$htmlname = 'accountid',
						$statut = 0,
						$filtre = '',
						$useempty = 0,
						$moreattrib = '' 
					);
			  print"</td>";
				
				print '<td>Le fichier contient une ligne d\'en-téte :</td>
				<td><input type="checkbox" name="entete" value="1" checked></td>
				
			  </tr>
			  
		</table>';
	print'<div width=100% style="text-align:center;"><input type="submit" class="button" name="add" value="'.$langs->trans("ComparerCSV").'"></div>';
	dol_fiche_end();
print '</form>';


llxFooter();

$db->close();
