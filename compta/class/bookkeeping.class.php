<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 *	\file       htdocs/compta/class/BookKeeping.class.php
 * 	\ingroup    compta
 * 	\brief      Fichier de la classe des comptes comptable
 * 	\version    $Id: BookKeeping.class.php,v 1.3 2011/08/03 00:46:33 eldy Exp $
 */


/** 	\class BookKeeping
 *    	\brief Classe permettant la gestion des comptes generaux de compta
 */

class BookKeeping
{
  var $db ;

  var $id ;
  var $doc_date;
  var $doc_type;
  var $fk_doc;
  var $fk_docdet;
  var $fk_compte;
  var $numero_compte;
  var $label_compte;
  var $debit;
  var $credit;
  var $montant;
  var $sens;
  var $fk_user_author;

  /**
   *    \brief  Constructeur de la classe
   *    \param  DB          handler acces base de donnees
   *    \param  id          id compte (0 par defaut)
   */

	function BookKeeping($db)
	{
		$this->db = $db;
	}

	/**
	*    \brief  Insere une ligne dans bookkeeping
	*    \param  user utilisateur qui effectue l'insertion
	*/

	function create()
	{
	  global $conf, $user, $langs;
	  
	  // first check if line not yet in bookkeeping
	  $sql = "SELECT count(*)";
	  $sql .= " FROM ".MAIN_DB_PREFIX."bookkeeping ";
	  $sql .= " WHERE doc_type = '".$this->doc_type."'";
	  $sql .= " AND fk_docdet = ".$this->fk_docdet;
	  $sql .= " AND numero_compte = '".$this->numero_compte."'";

	  $resql = $this->db->query($sql) ;

	  if ( $resql )
	    {
			$row = $this->db->fetch_array($resql);
			if ($row[0] == 0)
			{
			  $sql = "INSERT INTO ".MAIN_DB_PREFIX."bookkeeping (doc_date, doc_type, doc_ref,fk_doc,fk_docdet,fk_compte,numero_compte,label_compte,debit,credit,montant,sens,fk_user_author)";
			  $sql .= " VALUES ('".$this->doc_date."','".$this->doc_type."','".$this->doc_ref."',".$this->fk_doc.",".$this->fk_docdet.",".$this->fk_compte.",'".$this->numero_compte."','".$this->label_compte."',".$this->debit.",".$this->credit.",".$this->montant.",'".$this->sens."',".$user->id.")";

			  $resql = $this->db->query($sql);
			  if ( $resql )
			    {
			      $id = $this->db->last_insert_id(MAIN_DB_PREFIX."bookkeeping");

			      if ($id > 0)
				{
				  $this->id = $id;
				  $result = 0;
				}
			      else
				{
				  $result = -2;
				  dol_syslog("BookKeeping::Create Erreur $result lecture ID");
				}
			    }
			  else
			    {
			      $result = -1;
			      dol_syslog("BookKeeping::Create Erreur $result INSERT Mysql");
			    }
			}
			  else
			{
			  $result = -3;
			  dol_syslog("BookKeeping::Create Erreur $result SELECT Mysql");
			}
	    }
	  else
	    {
	      $result = -5;
	      dol_syslog("BookKeeping::Create Erreur $result SELECT Mysql");
	    }

      return $result;
    }
    
}
?>
