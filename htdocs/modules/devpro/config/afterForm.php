<?php
 // Supprime les donneés chargées
 Session::delete('data');
 // Supprime le message flash
 Session::delete('msgConfirm');
 Session::delete('msgError');
 Session::delete('msgAlert');
?>
