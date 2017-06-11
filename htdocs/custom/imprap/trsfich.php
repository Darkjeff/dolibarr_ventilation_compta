<?php
// Testons si le fichier a bien été envoyé et s'il n'y a pas d'erreur
if (isset($_FILES['csv']) AND $_FILES['csv']['error'] == 0)
{
        // Testons si le fichier n'est pas trop gros
        if ($_FILES['csv']['size'] <= 1000000)
        {
                // Testons si l'extension est autorisée
                $infosfichier = pathinfo($_FILES['csv']['name']);
                $extension_upload = $infosfichier['extension'];
                $extensions_autorisees = array('csv');
                if (in_array($extension_upload, $extensions_autorisees))
                {
                        // On peut valider le fichier et le stocker définitivement
                        move_uploaded_file($_FILES['csv']['tmp_name'], 'uploads/' . basename($_FILES['csv']['name']));
                        echo "L'envoi a bien été effectué !";
						
						
				
	
	
	
                }
				else
				{
					echo'extension de ce fichier n\'exacte';
					
				}
        }
}


?>