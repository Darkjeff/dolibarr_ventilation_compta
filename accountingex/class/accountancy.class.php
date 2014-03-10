<?php
/* Copyright (C) 2005-2012 Christophe Battarel  <christophe.battarel@altairis.fr>
 * Copyright (C) 2005-2012 Agnes Rambaud        <agnes.rambaud@altairis.fr>
 * Copyright (C) 2011-2014 Alexandre Spangaro   <alexandre.spangaro@gmail.com>
 * Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com> 
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
 *	\file       accountingex/class/accountancy.class.php
 * 	\ingroup    Accounting Expert
 * 	\brief      Fichier de la classe comptabilité expert
 */
dol_include_once("/core/class/commonobject.class.php");

/**
 * \class 		Accountancy
 * \brief 		Classe permettant la gestion comptable
 */
class Accountancy
{
    var $db;
    var $error;

    var $rowid;
    var $fk_pcg_version;
    var $pcg_type;
    var $pcg_subtype;
    var $label;
    var $account_number;
    var $account_parent;
    
    /**
     *  Constructor
     *
     *  @param		DoliDB		$db		Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     *    Load list of accounts
     *
     *    @return	  $listaccounts
     */
    function load_accounts($db, $account_number)
    {
        global $conf;
        
    	  $sql = "SELECT a.rowid, a.fk_pcg_version, a.account_number, a.label, a.active";
    	  $sql.= " , s.rowid, s.pcg_version";
    	  $sql.= " FROM ".MAIN_DB_PREFIX."accountingaccount as a";
    	  $sql.= " ,".MAIN_DB_PREFIX."accountingsystem as s";
        $sql.= " WHERE s.pcg_version=a.fk_pcg_version AND a.active='1' AND s.rowid=".$conf->global->CHARTOFACCOUNTS;
        $sql.= " ORDER BY a.account_number";
        
        $result = $db->query($sql);
      	if ($result)
        {
            $num = $db->num_rows($result);
            $i = 1;
            $listaccounts = "<option value=\"0\"";
          		if (!$_POST["compte_ligne_".$ligne])
              {
          			$listaccounts .= " selected=\"true\"";
          			$compte_sel = "";
          		}
          		else 
              {
          			$compte_sel = $_POST["compte_ligne_".$ligne];
          		}
      		    
              $listaccounts .= ">&nbsp;</option>";
            
              while ($i <= $num)
              {
                  $obj = $db->fetch_object($result);
                  $listaccounts .= "<option value=\"$obj->rowid\"";
            			if ($obj->rowid == $compte_sel) $listaccounts .= " selected=\"true\"";
            			$listaccounts .= ">$obj->numero - $obj->libelle</option>\n";
                    $i++;
              }
              $db->free($result);
      		  return $listaccounts;
      	}
    }

}

/*!
    \file	htdocs/altacompta/classes/altacompta.class.php
    \ingroup	Module AltaCompta
    \brief	Définition des classes Compta
	\author Christophe Battarel <christophe.battarel@altairis.fr>
	\author Agnes Rambaud <agnes.rambaud@altairis.fr>
	\version	1.0 2011/10/05 09:55 Christophe Battarel
*/

class Piece
{

	var $db;
	var $rowid;
	var $journal;
	var $num_piece;
	var $date_piece;
	var $montant;
	var $libelle;
	var $tiers;
	var $code_origine;
	var $exportee;
	var $date_creation;
	var $reference;
	var $type_piece;
	var $nature;
	var $montant_tva;
	var $montant_ht;
	var $mrg;
	var $code_lettrage;
	var $lignes;
	
	function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     *    Load list of accounts
     *
     *    @return	  $listaccounts
     */
    function load_accounts($db, $lignes)
    {
        global $conf, $user;
        
    	  $sql = "SELECT a.rowid, a.fk_pcg_version, a.account_number, a.label, a.active";
    	  $sql.= " , s.rowid, s.pcg_version";
    	  $sql.= " FROM ".MAIN_DB_PREFIX."accountingaccount as a";
    	  $sql.= " ,".MAIN_DB_PREFIX."accountingsystem as s";
        $sql.= " WHERE s.pcg_version=a.fk_pcg_version AND a.active='1' AND s.rowid=".$conf->global->CHARTOFACCOUNTS;
        $sql.= " ORDER BY a.account_number";
        
        $result = $db->query($sql);
      	if ($result)
        {
            $num = $db->num_rows($result);
            $i = 1;
            $listaccounts = "<option value=\"0\"";
          		if (!$_POST["compte_ligne_".$ligne])
              {
          			$listaccounts .= " selected=\"true\"";
          			$compte_sel = "";
          		}
          		else 
              {
          			$compte_sel = $_POST["compte_ligne_".$ligne];
          		}
      		    
              $listaccounts .= ">&nbsp;</option>";
            
              while ($i <= $num)
              {
                  $obj = $db->fetch_object($result);
                  $listaccounts .= "<option value=\"$obj->rowid\"";
            			if ($obj->rowid == $compte_sel) $listaccounts .= " selected=\"true\"";
            			$listaccounts .= ">$obj->numero - $obj->libelle</option>\n";
                    $i++;
              }
              $db->free($result);
      		  return $listaccounts;
      	}
    }
	
	function Piece($db, $rowid='')
	{
		$this->db = $db;
		if ($rowid != '') return $this->fetch($rowid);
	}
	
	function fetch($rowid='')
	{
		if ($rowid != '')
		{
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."altacompta_pieces WHERE rowid = '".$rowid."'";
			$result = $this->db->query($sql);
			if ($result)
			{
				$obj = $this->db->fetch_object($result);
				$this->rowid = $rowid;
				$this->journal = $obj->journal;
				$this->num_piece = $obj->num_piece;
				$this->date_piece = $obj->date_piece;
				$this->montant = $obj->montant;
				$this->libelle = stripslashes($obj->libelle);
				$this->tiers = $obj->tiers;
				$this->code_origine = $obj->code_origine;
				$this->date_creation = $obj->date_creation;
				$this->exportee = $obj->exportee;
				$this->reference = $obj->reference;
				$this->type_piece = $obj->type_piece;
				$this->montant_tva = $obj->montant_tva;
				$this->montant_ht = $obj->montant_ht;
				$this->mrg=$obj->mrg;
			}
			else return null;
		}
		return $this;
	}

	function getNumPiece($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->num_piece;
	}

	function setLettrage($code_lettrage)
	{
		$this->code_lettrage = $lettrage;
		$this->db->query("UPDATE ".MAIN_DB_PREFIX."altacompta_pieces SET code_lettrage = '".$code_lettrage."', date_lettrage = now() WHERE rowid='".$this->rowid."'");
	}

	function nouvelle_piece($journal, $date_piece, $montant, $libelle, $tiers, $code_origine, $reference, $type_piece, $montant_ht, $montant_tva, $mrg)
	{
		$this->num_piece = $this->getNewPieceNumber($journal);

		$this->journal = $journal;
		$this->date_piece = substr($date_piece, 0, 10);
		$this->montant = number_format($montant, 2, '.', '');
		$this->libelle = $libelle;
		$this->tiers = $tiers;
		$this->code_origine = $code_origine;
		$this->exportee = 0;
		$this->reference = $reference;
		$this->type_piece = $type_piece;
		$this->montant_tva = number_format($montant_tva, 2, '.', '');
		$this->montant_ht = number_format($montant_ht, 2, '.', '');
		$this->mrg=$mrg;
			
		$sql="INSERT INTO ".MAIN_DB_PREFIX."altacompta_pieces (journal, num_piece, date_piece, montant, libelle, tiers, code_origine, exportee, date_creation, reference, type_piece, montant_ht, montant_tva, mrg) VALUES('$this->journal', '$this->num_piece', '$this->date_piece', '$this->montant', '".addslashes($this->libelle)."', '$this->tiers', '$this->code_origine', '$this->exportee', now(), '$this->reference', '$this->type_piece', '$this->montant_ht', '$this->montant_tva', '$this->mrg')";

		if ( $this->db->query($sql) ) {

			// recuperation id piece
			$this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.'altacompta_pieces');
			return $this->rowid;
		}
		else
		{
			dolibarr_print_error($this->db);
			return null;
		}
	}
	
	function getNewPieceNumber($journal) {
			$this->num_piece = null;
			// r�up�ation dernier numero de piece
			$sql = "SELECT compteur from ".MAIN_DB_PREFIX."altacompta_compteurs WHERE type='NUMPIECE' and parm='$journal'";
			$result = $this->db->query($sql);
			if ($result)
			{
				if ($this->db->num_rows($result))
				{
					$obj = $this->db->fetch_object($result);
					$this->num_piece = $obj->compteur;
					$this->num_piece = $this->num_piece + 1;
					$sql = "UPDATE ".MAIN_DB_PREFIX."altacompta_compteurs set compteur = '$this->num_piece' WHERE type='NUMPIECE' and parm='$journal'";
					$result = $this->db->query($sql);
				}
				else {
					$this->num_piece = 1;
					$sql = "INSERT INTO ".MAIN_DB_PREFIX."altacompta_compteurs (type, parm, compteur) VALUES('NUMPIECE', '$journal', '$this->num_piece')";
					$result = $this->db->query($sql);
				}	
			}
			return $this->num_piece;
	}
}

class Ligne {
	
	var $db;
	var $rowid;
	var $nature;
	var $id_piece;
	var $num_ligne;
	var $journal;
	var $num_piece;
	var $compte;
	var $montant;
	var $sens;
	var $libelle;

	function Ligne($db, $rowid='')
	{
		$this->db = $db;
		if ($rowid != '') return $this->fetch($rowid);
	}

	function fetch($rowid = '')
	{
		if ($rowid != '')
		{
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."altacompta_lignes WHERE rowid = '".$rowid."'";
			$result = $this->db->query($sql);
			if ($result)
			{
				$obj = $this->db->fetch_object($result);
				$this->rowid = $rowid;
				$this->id_piece = $obj->id_piece;
				$this->num_ligne = $obj->num_ligne;
				$this->journal = $obj->journal;
				$this->num_piece = $obj->num_piece;
				$this->compte = $obj->compte;
				$this->montant = $obj->montant;
				$this->sens = $obj->sens;
				$this->libelle = stripslashes($obj->libelle);
				$this->nature = $obj->nature;
				$this->date_creation = $obj->date_creation;
			}
			else return null;
		}
		return $this;
	}


	function nouvelle_ligne($id_piece, $num_ligne, $journal, $num_piece, $nature, $compte, $montant, $sens, $libelle)
	{
		$this->id_piece = $id_piece;
		$this->num_ligne = $num_ligne;
		$this->journal = $journal;
		$this->num_piece = $num_piece;
		$this->compte = $compte;
		$this->montant = number_format($montant, 2, '.', '');
		$this->sens = $sens;
		$this->libelle = $libelle;
		$this->nature = $nature;
		
		$sql="INSERT INTO ".MAIN_DB_PREFIX."altacompta_lignes (id_piece, num_ligne, journal, num_piece, compte, montant, sens, date_creation, libelle, nature) VALUES('$this->id_piece', '$this->num_ligne', '$this->journal', '$this->num_piece', '$this->compte', '$this->montant', '$this->sens', now(), '".addslashes($this->libelle)."', '$this->nature')";

		if ( $this->db->query($sql) ) {

			// recuperation id ligne
			$this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.'altacompta_lignes');
			return $this->rowid;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
}	

class Type_Piece 
{
	var $db;
	var $rowid;
	var $libelle;
	var $journal;
	
	function Type_Piece($db, $rowid='')
	{
		$this->db = $db;
		if ($rowid != '') return $this->fetch($rowid);
	}
	function fetch($rowid='')
	{
		if ($rowid != '')
		{
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."altacompta_types_pieces WHERE rowid = '".$rowid."'";
			$result = $this->db->query($sql);
			if ($result)
			{
				$obj = $this->db->fetch_object($result);
			}
			else return null;
		}
		$this->rowid = $obj->rowid;
		$this->libelle = stripslashes($obj->libelle);
		$this->journal = $obj->journal;
		
		return $this;
	}

	function getLibelle($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->libelle;
	}
	function getJournal($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->journal;
	}
	function nouveauType_Piece($libelle, $journal)
	{
		$this->libelle = $libelle;
		$this->journal = $journal;
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."altacompta_types_pieces (libelle, journal) VALUES('".addslashes($this->libelle)."', '".$this->journal."')";
		if ( $this->db->query($sql) ) {

			// recuperation id ligne
			$this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.'altacompta_types_pieces');
			return $this->rowid;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
	function maj($libelle, $journal)
	{
		$sql = "UPDATE ".MAIN_DB_PREFIX."altacompta_types_pieces SET libelle = '".addslashes($libelle)."', journal = '".$journal."' WHERE rowid = '".$this->rowid."'";
		if ( $this->db->query($sql) ) {
  		$this->libelle = $libelle;
  		$this->journal = $journal;
			return $this->rowid;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
}
class Compte 
{
	var $db;
	var $rowid;
	var $libelle;
	var $numero;
	var $journal;
	
	function Compte($db, $rowid='')
	{
		$this->db = $db;
		if ($rowid != '') return $this->fetch($rowid);
	}
	function fetch($rowid='')
	{
		if ($rowid != '')
		{
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."altacompta_comptes_generaux WHERE rowid = '".$rowid."'";
			$result = $this->db->query($sql);
			if ($result)
			{
				$obj = $this->db->fetch_object($result);
			}
			else return null;
		}
		$this->rowid = $obj->rowid;
		$this->libelle = stripslashes($obj->libelle);
		$this->numero = $obj->numero;
		$this->journal = $obj->journal;
		
		return $this;
	}

	function getId($numero = '')
	{
		if ($numero != '')
		{
			$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."altacompta_comptes_generaux WHERE numero = '".$numero."'";
			$result = $this->db->query($sql);
			if ($result)
			{
				$obj = $this->db->fetch_object($result);
				return $obj->rowid;
			}
			else return null;
		}
		
		return null;
	}
	function getLibelle($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->libelle;
	}
	function getNumero($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->numero;
	}

	function getJournal($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->journal;
	}
	
	function nouveauCompte($numero, $libelle)
	{
		$this->numero = $numero;
		$this->libelle = $libelle;
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."altacompta_comptes_generaux (numero, libelle, date_creation, fk_user_author) VALUES('".$numero."', '".addslashes($libelle)."', now(), '".$user->login."')";
		if ( $this->db->query($sql) ) {

			// recuperation id ligne
			$this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.'altacompta_comptes_generaux');
			return $this->rowid;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
	function maj($numero, $libelle)
	{
		$sql = "UPDATE ".MAIN_DB_PREFIX."altacompta_comptes_generaux SET numero = '".$numero."', libelle = '".addslashes($libelle)."' WHERE rowid = '".$this->rowid."'";
		if ( $this->db->query($sql) ) {
  		$this->numero = $numero;
  		$this->libelle = $libelle;
			return $this->rowid;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
}

class Journal 
{
	var $db;
	var $code_journal;
	var $libelle;
	
	function Journal($db, $code_journal='')
	{
		$this->db = $db;
		if ($code_journal != '') return $this->fetch($code_journal);
	}
	function fetch($code_journal='')
	{
		if ($code_journal != '')
		{
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."altacompta_journaux WHERE code_journal = '".$code_journal."'";
			$result = $this->db->query($sql);
			if ($result)
			{
				$obj = $this->db->fetch_object($result);
			}
			else return null;
		}
		$this->code_journal = $obj->code_journal;
		$this->libelle = stripslashes($obj->libelle);
		
		return $this;
	}

	function getLibelle($code_journal = '')
	{
		if ($code_journal != '') $this->fetch($code_journal);
		return $this->libelle;
	}
	
	function nouveauJournal($code, $libelle)
	{
			$this->code_journal = $code;
			$this->libelle = $libelle;
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."altacompta_journaux (code_journal, libelle) VALUES('".$code."', '".addslashes($libelle)."')";
		if ( $this->db->query($sql) ) {

			// recuperation id ligne
			return $this->code_journal;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
	function maj($libelle)
	{
		$sql = "UPDATE ".MAIN_DB_PREFIX."altacompta_journaux SET libelle = '".addslashes($libelle)."' WHERE code_journal = '".$this->code_journal."'";
		if ( $this->db->query($sql) ) {
			$this->libelle = $libelle;
			return $this->code_journal;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
}

class Nature 
{
	var $db;
	var $rowid;
	var $type_nature;
	var $libelle;
	var $compte;
	var $tiers;
	
	function Nature($db, $rowid = '')
	{
		$this->db = $db;
		if ($rowid != '') return $this->fetch($rowid);
	}
	function fetch($rowid='')
	{
		if ($rowid != '') {
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."altacompta_natures WHERE rowid = '".$rowid."'";
			$result = $this->db->query($sql);
			if ($result)
			{
				$obj = $this->db->fetch_object($result);
			}
			else return null;
		}
		$this->rowid = $obj->rowid;
		$this->libelle = stripslashes($obj->libelle);
		$this->compte = $obj->id_compte_associe;
		$this->tiers = $obj->id_compte_tiers;
		$this->type_nature = $obj->type_nature;
		
		return $this;
	}

	function getLibelle($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->libelle;
	}
	function getCompte($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->compte;
	}
	function getTiers($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->tiers;
	}
	function getType($rowid = '')
	{
		if ($rowid != '') $this->fetch($rowid);
		return $this->type_nature;
	}
	function nouvelleNature($type, $libelle, $compte, $tiers=0)
	{
			$this->libelle = $libelle;
			$this->compte = $compte;
			$this->tiers = $tiers;
			$this->type_nature = $type;
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."altacompta_natures (type_nature, libelle, id_compte_associe, id_compte_tiers) VALUES('".$type."', '".addslashes($libelle)."', '".$compte."', '".$tiers."')";
		if ( $this->db->query($sql) ) {

			// recuperation id ligne
			$this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.'altacompta_natures');
			return $this->rowid;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
	function maj($type, $libelle, $compte, $tiers=0)
	{
		$sql = "UPDATE ".MAIN_DB_PREFIX."altacompta_natures SET type_nature = '".$type."', libelle = '".addslashes($libelle)."', id_compte_associe = '".$compte."', id_compte_tiers = '".$tiers."' WHERE rowid = '".$this->rowid."'";
		if ( $this->db->query($sql) ) {
			$this->libelle = $libelle;
			$this->compte = $compte;
			$this->tiers = $tiers;
			$this->type_nature = $type;
			return $this->rowid;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
}

class Lettrage 
{
	var $db;
	var $rowid;
	var $code_lettrage;
	var $date_lettrage;
	
	function Lettrage($db, $rowid = '')
	{
		$this->db = $db;
		if ($rowid != '') return $this->fetch($rowid);
	}
	function getNewLettrage()
	{
			$this->code_lettrage = null;
			// r�up�ation dernier numero de piece
			$sql = "SELECT compteur from ".MAIN_DB_PREFIX."altacompta_compteurs WHERE type='LETTRAGE'";
			$result = $this->db->query($sql);
			if ($result)
			{
				if ($this->db->num_rows($result))
				{
					$obj = $this->db->fetch_object($result);
					$this->code_lettrage = $obj->compteur;
					$this->code_lettrage++;
					$sql = "UPDATE ".MAIN_DB_PREFIX."altacompta_compteurs set compteur = '$this->code_lettrage' WHERE type='LETTRAGE'";
					$result = $this->db->query($sql);
				}
				else {
					$this->code_lettrage = 1;
					$sql = "INSERT INTO ".MAIN_DB_PREFIX."altacompta_compteurs (type, parm, compteur) VALUES('LETTRAGE', '', '$this->code_lettrage')";
					$result = $this->db->query($sql);
				}	
			}
			return $this->code_lettrage;
	}
}

class Param 
{
	var $db;
	var $rowid;
	var $cle;
	var $valeur;
	
	function Param($db, $cle='')
	{
		$this->db = $db;
		if ($cle != '') return $this->fetch($cle);
	}
	function fetch($cle='')
	{
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."altacompta_params";
		if ($cle != '')
		{
			$sql .= " WHERE cle = '".$cle."'";
			$result = $this->db->query($sql);
			if ($result)
			{
				$obj = $this->db->fetch_object($result);
			}
			else return null;
		}
		$this->cle = stripslashes($obj->cle);
		$this->valeur = stripslashes($obj->valeur);
		return $this;
	}

	function getValeur($cle = '')
	{
		if ($cle != '') $this->fetch($cle);
		return $this->valeur;
	}
	function setValeur($valeur)
	{
		$this->valeur = $valeur;
		$sql = "UPDATE ".MAIN_DB_PREFIX."altacompta_params";
		$sql .= " SET valeur = '".addslashes($this->valeur)."'";
		$sql .= "WHERE cle = '".addslashes($this->cle)."'";
		return $this->db->query($sql);
	}
	function nouveauParametre($cle, $valeur)
	{
			$this->cle = $cle;
			$this->valeur = $valeur;
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."altacompta_params (cle, valeur) VALUES('".addslashes($cle)."', '".addslashes($valeur)."')";
		if ( $this->db->query($sql) ) {

			// recuperation id ligne
			$this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX.'altacompta_params');
			return $this->rowid;
		}
		else
		{
			print $sql;
			dolibarr_print_error($this->db);
			return null;
		}
	}
}
?>
