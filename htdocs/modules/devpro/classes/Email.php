<?php
class Email{

    /*
     * Envoie un email
     * @param   string/array (mail destinataire)
     * @param   string (mail expéditeur)
     * @param   string (nom expéditeur)
     * @param   string (sujet)
     * @param   string (message)
     * @param   array (stmtp paramètres)
     * @return  bool
     */
    static function send($recipients,$sender,$sender_name,$subject,$msg,$smtp=null){

        include_once(_PS_SWIFT_DIR_.'Swift.php');
        include_once(_PS_SWIFT_DIR_.'Swift/Connection/SMTP.php');
        include_once(_PS_SWIFT_DIR_.'Swift/Connection/NativeMail.php');
        include_once(_PS_SWIFT_DIR_.'Swift/Plugin/Decorator.php');

        // via Smtp
        if(!empty($smtp)){
            // mode de cryptage
            $crypt = $smtp['crypt'];
            if($crypt==''){$crypt=Swift_Connection_SMTP::ENC_OFF;}
            elseif($crypt=='tls'){$crypt=Swift_Connection_SMTP::ENC_TLS;}
            else{$crypt=Swift_Connection_SMTP::ENC_SSL;}
            // connexion
            $Connection = new Swift_Connection_SMTP($smtp['server'],$smtp['port'],$crypt);
            $Connection->setUsername($smtp['user']);
            $Connection->setPassword($smtp['pw']);
        // via Mail simple
        }else{
            $Connection = new Swift_Connection_NativeMail();
        }

        $Swift = new Swift($Connection,Configuration::get('PS_MAIL_DOMAIN'));
        $subject = Tools::htmlentitiesDecodeUTF8($subject);
        $Msg = new Swift_Message($subject,$msg,'text/html');

        $RecipientsList = new Swift_RecipientList();
        if(is_array($recipients)){
            foreach($recipients as $recipient){
                $RecipientsList->addTo($recipient);
            }
        }else{
            $RecipientsList->addTo($recipients);
        }
        $nbMailsSend = $Swift->batchSend($Msg,$RecipientsList,new Swift_Address($sender,$sender_name));
        return $nbMailsSend;
    }

}
?>