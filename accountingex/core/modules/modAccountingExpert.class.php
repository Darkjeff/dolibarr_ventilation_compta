<?php
/* Copyright (C) 2010-2011 Regis Houssin        <regis@dolibarr.fr>
 * Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro   <alexandre.spangaro@gmail.com>
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
 *    \file       accountingex/core/modules/modAccountingExpert.class.php
 *    \ingroup    Accounting Expert 
 *    \brief      Module to activate Accounting Expert module
 */

dol_include_once("/core/modules/DolibarrModules.class.php");


/**
 *       \class      modAccountingExpert
 *       \brief      Classe de description et activation du module Accounting Expert
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
		$this->numero = 150000;
    
		$this->family = "financial";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		$this->description = "Gestion avancé de la comptabilité";

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '3.5.x';
    $this->revision = '2.0.0';

		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->special = 0;
		$this->picto = 'accountingex@accountingex';
		
		// Defined if the directory /mymodule/inc/triggers/ contains triggers or not
		//$this->triggers = 1;

		// Data directories to create when module is enabled
		$this->dirs = array('/accountingex/temp');

		// Config pages
		// $this->config_page_url = array('index.php@accountingex'); Deprecated - Need an admin page into the module directly - Not reserve for the admin
		
		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
    $this->conflictwith = array("modAccounting"); // List of modules are in conflict with this module
		$this->phpmin = array(5,2);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,4);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("accountingex@accountingex");

		// Constantes
		$this->const = array();
		$this->const[1] = array("ACCOUNTINGEX_SEPARATORCSV","string",",");
		$this->const[2] = array("ACCOUNTINGEX_ACCOUNT_SUSPENSE","chaine","471");
		$this->const[3] = array("ACCOUNTINGEX_SELL_JOURNAL","chaine","VTE");
		$this->const[4] = array("ACCOUNTINGEX_PURCHASE_JOURNAL","chaine","ACH");
		$this->const[5] = array("ACCOUNTINGEX_BANK_JOURNAL","chaine","BQ");
		$this->const[6] = array("ACCOUNTINGEX_SOCIAL_JOURNAL","chaine","SOC");    
		$this->const[7] = array("ACCOUNTINGEX_CASH_JOURNAL","chaine","CAI");    
		$this->const[8] = array("ACCOUNTINGEX_MISCELLANEOUS_JOURNAL","chaine","OD");
    $this->const[9] = array("ACCOUNTINGEX_ACCOUNT_TRANSFER_CASH","chaine","58");
    $this->const[10] = array("CHARTOFACCOUNTS","chaine","2");
    $this->const[11] = array("ACCOUNTINGEX_MODELCSV","chaine","0");
    $this->const[12] = array("ACCOUNTINGEX_LENGTH_GACCOUNT","chaine","");
		$this->const[13] = array("ACCOUNTINGEX_LENGTH_AACCOUNT","chaine","");
    $this->const[14] = array("LIMIT_LIST_VENTILATION","chaine","50");
		$this->const[15] = array("LIST_SORT_VENTILATION","chaine","DESC");
		
    
    // Css
    $this->module_parts = array('css' => array('/accountingex/css/accountingex.css.php'));
    		
		// Boxes
		$this->boxes = array();

		// Permissions
    $this->rights_class = 'accountingex';

		$this->rights = array();		// Permission array used by this module
		$r=0;

    $this->rights[$r][0] = 150001; 				// Permission id (must not be already used)
		$this->rights[$r][1] = 'Acces_module';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'access';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$this->rights[$r][5] = '';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;
    
		$this->rights[$r][0] = 150002; 				// Permission id (must not be already used)
		$this->rights[$r][1] = 'Administration_module';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'admin';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$this->rights[$r][5] = '';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;
    
    $this->rights[$r][0] = 150010; 				// Permission id (must not be already used)
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
								'url'=>'/accountingex/customer/index.php',
								'langs'=>'accountingex@accountingex',
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
								'url'=>'/accountingex/customer/index.php',
								'langs'=>'accountingex@accountingex',
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
								'url'=>'/accountingex/customer/liste.php',
								'langs'=>'accountingex@accountingex',
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
								'url'=>'/accountingex/customer/lignes.php',
								'langs'=>'accountingex@accountingex',
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
								'url'=>'/accountingex/supplier/index.php',
								'langs'=>'accountingex@accountingex',
								'position'=>110,
								'enabled'=>'$conf->fournisseur->enabled',
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'r=4',
								'type'=>'left',
								'titre'=>'ToDispatch',
								'mainmenu'=>'accounting',
								'url'=>'/accountingex/supplier/liste.php',
								'langs'=>'accountingex@accountingex',
								'position'=>111,
								'enabled'=>'$conf->fournisseur->enabled',
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
		
		$this->menu[$r]=array(  'fk_menu'=>'r=4',
								'type'=>'left',
								'titre'=>'Dispatched',
								'mainmenu'=>'accounting',
								'url'=>'/accountingex/supplier/lignes.php',
								'langs'=>'accountingex@accountingex',
								'position'=>112,
								'enabled'=>'$conf->fournisseur->enabled',
								'perms'=>1,
								'target'=>'',
								'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'r=0',
								'type'=>'left',
								'titre'=>'Journaux',
								'mainmenu'=>'accounting',
								//'url'=>'',
								'langs'=>'accountingex@accountingex',
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
		            'url'=>'/accountingex/journal/sellsjournal.php',
		            'langs'=>'accountingex@accountingex',
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
		            'url'=>'/accountingex/journal/purchasesjournal.php',
		            'langs'=>'accountingex@accountingex',
		            'position'=>122,
		            'enabled'=>1,
		            'perms'=>'$conf->fournisseur->enabled',
		            'target'=>'',
		            'user'=>0);
    $r++;
    
    $this->menu[$r]=array(  'fk_menu'=>'r=7',
		            'type'=>'left',
		            'titre'=>'BankJournal',
		            'mainmenu'=>'accounting',
		            'url'=>'/accountingex/journal/bankjournal.php',
		            'langs'=>'accountingex@accountingex',
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
		            'url'=>'/accountingex/journal/cashjournal.php',
		            'langs'=>'accountingex@accountingex',
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
								'url'=>'/accountingex/bookkeeping/liste.php',
								'langs'=>'accountingex@accountingex',
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
		            'url'=>'/accountingex/bookkeeping/listebyyear.php',
		            'langs'=>'accountingex@accountingex',
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
		            'url'=>'/accountingex/bookkeeping/balancebymonth.php',
		            'langs'=>'accountingex@accountingex',
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
								'url'=>'/accountingex/admin/index.php',
								'langs'=>'accountingex@accountingex',
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
		            'url'=>'/accountingex/admin/index.php',
		            'langs'=>'accountingex@accountingex',
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
		            'url'=>'/accountingex/admin/account.php',
		            'langs'=>'accountingex@accountingex',
		            'position'=>142,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->admin',
		            'target'=>'',
		            'user'=>0);
     $r++;
     
     $this->menu[$r]=array(  'fk_menu'=>'r=17',
		            'type'=>'left',
		            'titre'=>'Menuaccount',
		            'mainmenu'=>'accounting',
		            'url'=>'/accountingex/admin/account.php',
		            'langs'=>'accountingex@accountingex',
		            'position'=>143,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->admin',
		            'target'=>'',
		            'user'=>0);
     $r++;
     
     $this->menu[$r]=array(  'fk_menu'=>'r=17',
		            'type'=>'left',
		            'titre'=>'Menuthirdpartyaccount',
		            'mainmenu'=>'accounting',
		            'url'=>'/accountingex/admin/thirdpartyaccount.php',
		            'langs'=>'accountingex@accountingex',
		            'position'=>144,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->admin',
		            'target'=>'',
		            'user'=>0);
     $r++;
     
     $this->menu[$r]=array(  'fk_menu'=>'r=15',
		            'type'=>'left',
		            'titre'=>'MenuTools',
		            'mainmenu'=>'accounting',
		            'url'=>'/accountingex/admin/productaccount.php',
		            'langs'=>'accountingex@accountingex',
		            'position'=>145,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->admin',
		            'target'=>'',
		            'user'=>0);
     $r++;
     
     /*
     $this->menu[$r]=array(  'fk_menu'=>'r=14',
		            'type'=>'left',
		            'titre'=>'Fiscalyear',
		            'mainmenu'=>'accounting',
		            'url'=>'/accountingex/admin/fiscalyear.php',
		            'langs'=>'accountingex@accountingex',
		            'position'=>145,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->dev',
		            'target'=>'',
		            'user'=>0);
     $r++;
     
     $this->menu[$r]=array(  'fk_menu'=>'r=14',
		            'type'=>'left',
		            'titre'=>'Journaux',
		            'mainmenu'=>'accounting',
		            'url'=>'/accountingex/admin/journaux.php',
		            'langs'=>'accountingex@accountingex',
		            'position'=>146,
		            'enabled'=>1,
		            'perms'=>'$user->rights->accountingex->dev',
		            'target'=>'',
		            'user'=>0);
     $r++;
     */
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
		return $this->_load_tables('/accountingex/sql/');
	}

}
?>
