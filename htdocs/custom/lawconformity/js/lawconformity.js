/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
    $url = $(location).attr('href');
    if($url.match('htdocs/compta/paiement/card.php')){
    $('.butActionDelete').remove();
    }
    
    if($url.match('htdocs/compta/bank/ligne.php')){
    //
    $('input[name="amount"]').attr('disabled','disabled');
   
    $('input[name="amount"]').change(function(){
        $('.button').remove();
        $('input[name="action"]').remove();
        $('input[name="id"]').remove();
        $('input[name="token"]').remove();
        //$('form[name="update"]').remove();
    });
    }
    
});

