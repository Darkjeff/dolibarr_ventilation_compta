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
 * \file htdocs/compta/class/BookKeeping.class.php
 * \ingroup compta
 * \brief Fichier de la classe des comptes comptable
 * \version $Id: BookKeeping.class.php,v 1.3 2011/08/03 00:46:33 eldy Exp $
 */

/**
 * \class BookKeeping
 * \brief Classe permettant la gestion des comptes generaux de compta
 */
class BookKeeping {
	var $db;
	var $id;
	var $doc_date;
	var $doc_type;
	var $doc_ref;
	var $fk_doc;
	var $fk_docdet;
	var $code_tiers;
	var $numero_compte;
	var $label_compte;
	var $debit;
	var $credit;
	var $montant;
	var $sens;
	var $fk_user_author;
	var $code_journal;
	var $piece_num;
	
	var $linesexport=array();
	
	/**
	 * \brief Constructeur de la classe
	 * \param DB handler acces base de donnees
	 * \param id id compte (0 par defaut)
	 */
	function BookKeeping($db) {
		$this->db = $db;
	}
	
	/**
	 * \brief Insere une ligne dans bookkeeping
	 * \param user utilisateur qui effectue l'insertion
	 */
	function create() {
		global $conf, $user, $langs;
		
		$this->piece_num=0;
		
		// first check if line not yet in bookkeeping
		$sql = "SELECT count(*)";
		$sql .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
		$sql .= " WHERE doc_type = '" . $this->doc_type . "'";
		$sql .= " AND fk_docdet = " . $this->fk_docdet;
		$sql .= " AND numero_compte = '" . $this->numero_compte . "'";
		
		dol_syslog (get_class($this).":: create sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		
		if ($resql) {
			$row = $this->db->fetch_array ( $resql );
			if ($row [0] == 0) {
				
				// Determine piece_num
				$sqlnum = "SELECT piece_num";
				$sqlnum .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
				$sqlnum .= " WHERE doc_type = '" . $this->doc_type . "'";
				$sqlnum .= " AND fk_docdet = '" . $this->fk_docdet. "'";
				$sqlnum .= " AND doc_ref = '" . $this->doc_ref. "'";
				
				dol_syslog (get_class($this).":: create sqlnum=" . $sqlnum, LOG_DEBUG );
				$resqlnum = $this->db->query ( $sqlnum );
				if ($resqlnum) {
					$objnum = $this->db->fetch_object ( $resqlnum );
					$this->piece_num=$objnum->piece_num;
				}
				dol_syslog (get_class($this).":: create this->piece_num=" . $this->piece_num, LOG_DEBUG );
				if (empty($this->piece_num)) {
					$sqlnum = "SELECT MAX(piece_num)+1 as maxpiecenum";
					$sqlnum .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
					
					dol_syslog (get_class($this).":: create sqlnum=" . $sqlnum, LOG_DEBUG );
					$resqlnum = $this->db->query ( $sqlnum );
					if ($resqlnum) {
						$objnum = $this->db->fetch_object ( $resqlnum );
						$this->piece_num=$objnum->maxpiecenum;
					}
				}
				dol_syslog (get_class($this).":: create this->piece_num=" . $this->piece_num, LOG_DEBUG );
				if (empty($this->piece_num)) {
					$this->piece_num=1;
				}
				
				
				$now = dol_now ();
				$sql = "INSERT INTO " . MAIN_DB_PREFIX . "bookkeeping (doc_date, doc_type, doc_ref,fk_doc,fk_docdet,code_tiers,numero_compte,label_compte,debit,credit,montant,sens,fk_user_author,import_key,code_journal,piece_num)";
				$sql .= " VALUES ('" . $this->doc_date . "','" . $this->doc_type . "','" . $this->doc_ref . "'," . $this->fk_doc . "," . $this->fk_docdet . ",'" . $this->code_tiers . "','" . $this->numero_compte . "','" . $this->label_compte . "'," . $this->debit . "," . $this->credit . "," . $this->montant . ",'" . $this->sens . "'," . $user->id . ", '" . $now . "','" . $this->code_journal . "',".$this->piece_num.")";
				
				$resql = $this->db->query ( $sql );
				if ($resql) {
					$id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "bookkeeping" );
					
					if ($id > 0) {
						$this->id = $id;
						$result = 0;
					} else {
						$result = - 2;
						dol_syslog ( "BookKeeping::Create Erreur $result lecture ID" );
					}
				} else {
					$result = - 1;
					dol_syslog ( "BookKeeping::Create Erreur $result INSERT Mysql" );
				}
			} else {
				$result = - 3;
				dol_syslog ( "BookKeeping::Create Erreur $result SELECT Mysql" );
			}
		} else {
			$result = - 5;
			dol_syslog ( "BookKeeping::Create Erreur $result SELECT Mysql" );
		}
		
		return $result;
	}
	
	/**
	 * \brief Delete bookkepping by importkey
	 */
	function delete_by_importkey($importkey) {
		$this->db->begin ();
		
		// first check if line not yet in bookkeeping
		$sql = "DELETE";
		$sql .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
		$sql .= " WHERE import_key = '" . $importkey . "'";
		
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::delete " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return 1;
		}
	}
	
	
	/**
	 * \brief Delete bookkepping by importkey
	 */
	function export_bookkeping($model='ebp') {
		
		$sql= "SELECT rowid, doc_date, doc_type, " ;
		$sql.= "doc_ref, fk_doc, fk_docdet, code_tiers, ";
		$sql.= "numero_compte, label_compte, debit, credit, ";
		$sql.= " montant, sens, fk_user_author, import_key, code_journal, piece_num  ";
		$sql.= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
		
		$resql = $this->db->query ( $sql );
		
		dol_syslog(get_class($this)."::export_bookkeping sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->linesexport=array();
				
			$num = $this->db->num_rows($resql);
			while ($obj = $this->db->fetch_object($resql))
			{
				$line = new BookKeepingLine();
		
				$line->id = $obj->rowid;
		
				$line->doc_date = $this->db->jdate($obj->doc_date);
				$line->doc_type = $obj->doc_type;
				$line->doc_ref = $obj->doc_ref;
				$line->fk_doc = $obj->fk_doc;
				$line->fk_docdet = $obj->fk_docdet;
				$line->code_tiers = $obj->code_tiers;
				$line->numero_compte = $obj->numero_compte;
				$line->label_compte = $obj->label_compte;
				$line->debit = $obj->debit;
				$line->credit = $obj->credit;
				$line->montant = $obj->montant;
				$line->sens = $obj->sens;
				$line->code_journal = $obj->code_journal;
				$line->piece_num = $obj->piece_num;

				$this->linesexport[]=$line;
		
			}
			$this->db->free($resql);
		
			return $num;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::export_bookkeping ".$this->error, LOG_ERR);
			return -1;
		}

	}
}

class BookKeepingLine {
	var $id;
	var $doc_date;
	var $doc_type;
	var $doc_ref;
	var $fk_doc;
	var $fk_docdet;
	var $code_tiers;
	var $numero_compte;
	var $label_compte;
	var $debit;
	var $credit;
	var $montant;
	var $sens;
	var $fk_user_author;
	var $code_journal;
	var $piece_num;
}
?>
