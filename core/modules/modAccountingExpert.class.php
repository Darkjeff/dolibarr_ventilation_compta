<?php
/* Copyright (C) 2010-2011 Regis Houssin        <regis@dolibarr.fr>
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
 */

/**
 *    \file       core/modules/modAccountingExpert.class.php
 *    \ingroup    Accounting Expert 
 *    \brief      Module to activate Accounting Expert module
 */

include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 *       \class      modVentilation
 *       \brief      Classe de description et activation du module Ventilation
 */
class modAccountingExpert extends DolibarrModules
{
	/**
	 *		\brief	Constructeur. definit les noms, constantes et boites
	 * 		\param	DB	handler d'acces base
	 */
	function modAccountingExpert ($DB)
	{
		$this->db = $DB;
		$this->numero = 62000;
    
		$this->family = "financial";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		$this->description = "Gestion avancé de la comptabilité";

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = 'Développement';
    $this->revision = '';

		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->special = 0;
		$this->picto = 'accounting@ventilation';
		
		// Defined if the directory /mymodule/inc/triggers/ contains triggers or not
		//$this->triggers = 1;

		// Data directories to create when module is enabled
		$this->dirs = array();

		// Config pages
		// $this->config_page_url = array('index.php@ventilation'); Deprecated - Need an admin page into the module directly - Not reserve for the admin
		
		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
    $this->conflictwith = array("modAccounting"); // List of modules are in conflict with this module
		$this->phpmin = array(5,2);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,3);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("ventilation@ventilation");

		// Constantes
		$this->const = array();
		$this->const[2] = array("VENTILATION_ACCOUNT_SUSPENSE","chaine","47100000");
		$this->const[3] = array("VENTILATION_SELL_JOURNAL","chaine","VTE");
		$this->const[4] = array("VENTILATION_PURCHASE_JOURNAL","chaine","ACH");
		$this->const[5] = array("VENTILATION_BANK_JOURNAL","chaine","BNK");
		$this->const[6] = array("VENTILATION_SOCIAL_JOURNAL","chaine","SOC");    
		$this->const[7] = array("VENTILATION_CASH_JOURNAL","chaine","CAI");    
		$this->const[8] = array("VENTILATION_MISCELLANEOUS_JOURNAL","chaine","OD");
    $this->const[9] = array("VENTILATION_ACCOUNT_TRANSFER_CASH","chaine","58000000");
    		
		// Boxes
		$this->boxes = array();

		// Permissions
    $this->rights_class = 'accountingex';

		$this->rights = array();		// Permission array used by this module
		$r=0;

    $this->rights[$r][0] = 62001; 				// Permission id (must not be already used)
		$this->rights[$r][1] = 'Acces_module';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'access';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$this->rights[$r][5] = '';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;
    
		$this->rights[$r][0] = 62002; 				// Permission id (must not be already used)
		$this->rights[$r][1] = 'Administration_module';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'admin';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$this->rights[$r][5] = '';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;
    
    $this->rights[$r][0] = 62010; 				// Permission id (must not be already used)
		$this->rights[$r][1] = 'Développement';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'dev';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$this->rights[$r][5] = '';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;
		
		// Main menu entries
		$this->menus = array();
		$r=0;

		$this->menu[$r]=array('fk_menu'=>0,
								'type'=>'top',
								'titre'=>'Accounting',
								'mainmenu'=>'accounting',
								'leftmenu'=>'1',
								'url'=>'/ventilation/index.php',
								'langs'=>'ventilation@ventilation',
								'position'=>100,
								'perms'=>'$user->rights->accountingex->access',
								'enabled'=>'$conf->accountingexpert->enabled',
								'target'=>'',
								'user'=>0);
		$r++;
		
	
  
		
	
		
	  $this->menu[$r]=array(	'fk_menu'=>'r=0',
								'type'=>'left',
								'titre'=>'CustomersVentilation',
								'mainmenu'=>'accounting',
								'url'=>'/ventilation/index.php',
								'langs'=>'ventilation@ventilation',
								'position'=>101,
								'enabled'=>1,
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'r=1',
								'type'=>'left',
								'titre'=>'ToDispatch',
								'mainmenu'=>'accounting',
								'url'=>'/ventilation/liste2.php',
								'langs'=>'ventilation@ventilation',
								'position'=>102,
								'enabled'=>1,
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'r=1',
								'type'=>'left',
								'titre'=>'Dispatched',
								'mainmenu'=>'accounting',
								'url'=>'/ventilation/lignes.php',
								'langs'=>'ventilation@ventilation',
								'position'=>103,
								'enabled'=>1,
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;

	  $this->menu[$r]=array(	'fk_menu'=>'r=0',
								'type'=>'left',
								'titre'=>'SuppliersVentilation',
								'mainmenu'=>'accounting',
								'url'=>'/ventilation/fournisseur/index.php',
								'langs'=>'ventilation@ventilation',
								'position'=>110,
								'enabled'=>1,
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'r=4',
								'type'=>'left',
								'titre'=>'ToDispatch',
								'mainmenu'=>'accounting',
								'url'=>'/ventilation/fournisseur/liste.php',
								'langs'=>'ventilation@ventilation',
								'position'=>111,
								'enabled'=>1,
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
		
		$this->menu[$r]=array(  'fk_menu'=>'r=4',
								'type'=>'left',
								'titre'=>'Dispatched',
								'mainmenu'=>'accounting',
								'url'=>'/ventilation/fournisseur/lignes.php',
								'langs'=>'ventilation@ventilation',
								'position'=>112,
								'enabled'=>1,
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'r=0',
								'type'=>'left',
								'titre'=>'Journaux',
								'mainmenu'=>'accounting',
								'url'=>'/ventilation/journal/index.php',
								'langs'=>'ventilation@ventilation',
								'position'=>120,
								'enabled'=>1,
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
		
		$this->menu[$r]=array(  'fk_menu'=>'r=7',
		            'type'=>'left',
		            'titre'=>'Journal des ventes',
		            'mainmenu'=>'accounting',
		            'url'=>'/ventilation/journal/sellsjournal.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>121,
		            'enabled'=>1,
		            'perms'=>1,
		            'target'=>'',
		            'user'=>0);
    $r++;
		
    $this->menu[$r]=array(  'fk_menu'=>'r=7',
		            'type'=>'left',
		            'titre'=>'Journal des achats',
		            'mainmenu'=>'accounting',
		            'url'=>'/ventilation/journal/purchasesjournal.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>122,
		            'enabled'=>1,
		            'perms'=>1,
		            'target'=>'',
		            'user'=>0);
    $r++;
    
    $this->menu[$r]=array(  'fk_menu'=>'r=7',
		            'type'=>'left',
		            'titre'=>'BankJournal',
		            'mainmenu'=>'accounting',
		            'url'=>'/ventilation/journal/bankjournal.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>123,
		            'enabled'=>1,
		            'perms'=>1,
		            'target'=>'',
		            'user'=>0);
    $r++;
    
    $this->menu[$r]=array(  'fk_menu'=>'r=7',
		            'type'=>'left',
		            'titre'=>'CashJournal',
		            'mainmenu'=>'accounting',
		            'url'=>'/ventilation/journal/cashjournal.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>124,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->dev',
		            'target'=>'',
		            'user'=>0);
    $r++;
				
		$this->menu[$r]=array(	'fk_menu'=>'r=0',
								'type'=>'left',
								'titre'=>'Bookkeeping',
								'mainmenu'=>'accounting',
								'url'=>'/ventilation/bookkeeping/liste.php',
								'langs'=>'ventilation@ventilation',
								'position'=>130,
								'enabled'=>1,
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
      
		$this->menu[$r]=array(  'fk_menu'=>'r=12',
								'type'=>'left',
								'titre'=>'ByYear',
								'mainmenu'=>'accounting',
		            'url'=>'/ventilation/bookkeeping/listebyyear.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>131,
		            'enabled'=>1,
		            'perms'=>1,
		            'target'=>'',
		            'user'=>0);
		$r++;
      
    $this->menu[$r]=array(  'fk_menu'=>'r=12',
		            'type'=>'left',
		            'titre'=>'Balance mensuelle',
		            'mainmenu'=>'accounting',
		            'url'=>'/ventilation/bookkeeping/balancebymonth.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>132,
		            'enabled'=>1,
		            'perms'=>1,
		            'target'=>'',
		            'user'=>0);
     $r++;
           	
     // Parameters Menu
     $this->menu[$r]=array(	'fk_menu'=>'r=0',
								'type'=>'left',
								'titre'=>'Parameters',
								'mainmenu'=>'accounting',
								'url'=>'/ventilation/admin/index.php',
								'langs'=>'ventilation@ventilation',
								'position'=>140,
								'enabled'=>1,
								'perms'=>'$user->rights->accountingex->admin',
								'target'=>'',
								'user'=>0);
		 $r++;
      
		 $this->menu[$r]=array(  'fk_menu'=>'r=15',
								'type'=>'left',
								'titre'=>'Globalparameters',
								'mainmenu'=>'accounting',
		            'url'=>'/ventilation/admin/index.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>141,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->admin',
		            'target'=>'',
		            'user'=>0);
		 $r++;
      
     $this->menu[$r]=array(  'fk_menu'=>'r=15',
		            'type'=>'left',
		            'titre'=>'Chartofaccounts',
		            'mainmenu'=>'accounting',
		            'url'=>'/ventilation/admin/chartofaccounts.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>142,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->admin',
		            'target'=>'',
		            'user'=>0);
     $r++;
     
     	$this->menu[$r]=array(  'fk_menu'=>'r=15',
		            'type'=>'left',
		            'titre'=>'thirdpartyaccount',
		            'mainmenu'=>'accounting',
		            'url'=>'/ventilation/thirdpartyaccount/liste.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>143,
		            'enabled'=>1,
		            'perms'=>1,
		            'target'=>'',
		            'user'=>0);
    $r++;
     
     $this->menu[$r]=array(  'fk_menu'=>'r=15',
		            'type'=>'left',
		            'titre'=>'Fiscalyear',
		            'mainmenu'=>'accounting',
		            'url'=>'/ventilation/admin/fiscalyear.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>144,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->dev',
		            'target'=>'',
		            'user'=>0);
     $r++;
     
     $this->menu[$r]=array(  'fk_menu'=>'r=15',
		            'type'=>'left',
		            'titre'=>'Journaux',
		            'mainmenu'=>'accounting',
		            'url'=>'/ventilation/admin/journaux.php',
		            'langs'=>'ventilation@ventilation',
		            'position'=>145,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->dev',
		            'target'=>'',
		            'user'=>0);
     $r++;
  }


	/**
     *		\brief      Function called when module is enabled.
     *					The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *					It also creates data directories.
	 *      \return     int             1 if OK, 0 if KO
     */
	function init()
	{
		$sql = array();
		
		if ($this->load_tables() < 0)
		    return -1;
		
		

		return $this->_init($sql);
	}

	/**
	 *		\brief		Function called when module is disabled.
 	 *              	Remove from database constants, boxes and permissions from Dolibarr database.
 	 *					Data directories are not deleted.
	 *      \return     int             1 if OK, 0 if KO
 	 */
	function remove()
	{
		$sql = array();

		return $this->_remove($sql);
	}
	
	/**
	 *		\brief		Create tables and keys required by module
	 *					This function is called by this->init.
	 * 		\return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables('/ventilation/sql/');
	}

}
?>
