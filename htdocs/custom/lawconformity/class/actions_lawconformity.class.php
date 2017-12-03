<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class Actionslawconformity {

    function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager) {
        if (in_array('invoicecard', explode(':', $parameters['context']))) {
            global $conf, $user, $langs, $object, $hookmanager, $db;
//echo "<pre>".print_r($object->date_validation,1)."</pre>";
                if($object->date_validation != ""){
                    print '<script type="text/javascript">';
                    print "$(document).ready(function() {
                            $('.butAction').each(function(){
                            link = $(this).attr('href');
                            
                            if(link.match('modif')){
                                $(this).remove();
                                $('.butActionDelete').addClass('butActionRefused');
                                $(this).addClass('butActionRefused');
                                $(this).attr('href','#');
                                $('.butActionDelete').attr('href','#');
                            } });
                            
                        });";
                    print '</script>';
                }
            
        }
        if (in_array('paiementcard', explode(':', $parameters['context']))) {
            global $conf, $user, $langs, $object, $hookmanager, $db;
//echo "<pre>".print_r($object->date_validation,1)."</pre>";
                    print '<script type="text/javascript">';
                    print "$(document).ready(function() {
                        alert('toto');
                            $('.butAction').each(function(){
                            link = $(this).attr('href');
                            
                            if(link.match('modif')){
                                $(this).remove();
                                $('.butActionDelete').addClass('butActionRefused');
                                $(this).addClass('butActionRefused');
                                $(this).attr('href','#');
                                $('.butActionDelete').attr('href','#');
                            } });
                            
                        });";
                    print '</script>';
                
            
        }
    }

}
