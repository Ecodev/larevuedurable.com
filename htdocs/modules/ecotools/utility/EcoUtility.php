<?php

class EcoUtility
{

    public static function log($message, $file = '', $line = '', $logName = 'ecolog')
    {
        $msg = (new DateTime())->format('Y-m-d H:i:s') . ' : ' . $file . ':' . $line . "\r\n";

        if (is_array($message)) {
            $message = implode("\r\n", $message);
        }
        $msg .= $message . "\r\n";
        error_log($msg, 3, _PS_ROOT_DIR_ . '/../logs/' . $logName .  '.txt');
    }

    public static function replaceMailColor($text, $color = "FF0000")
    {
        return preg_replace("/((?<!background-|bg)color\s*[:=]\s*\"?#)([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})(\"?[;]?)/u", "$1$color$3", $text);
    }

    /**
     * Debug function to generate a string from an array
     * @param $array
     * @param int $deep
     * @return string
     */
    public static function arrayToString($array, $deep = 0)
    {
        $string = '';
        $tab = "";
        for ($i = 0; $i <= $deep; $i++) {
            $tab .= "\t";
        }
        foreach ($array as $key => $arr) {
            if (is_array($arr)) {
                $string .= $tab . $key . " => \n" . Tools::arrayToString($arr, $deep + 1);
            } else {
                $string .= $tab . $key . ' => ' . $arr . ",\n";
            }
        }

        return $string;
    }

}
