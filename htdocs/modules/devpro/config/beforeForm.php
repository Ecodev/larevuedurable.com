<?php
    echo
    Session::read('msgConfirm').
    Session::read('msgError').
    Session::read('msgAlert');
?>
