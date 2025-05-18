<?php
/*
* ip_in_range.php - Función para determinar si una IP está en un rango específico.
* Formatos válidos:
* 1. Wildcard:     1.2.3.*
* 2. CIDR:         1.2.3/24  o  1.2.3.4/255.255.255.0
* 3. Rango:        1.2.3.0-1.2.3.255
* Autor original: Paul Gregg
*/

// Devuelve un binario de 32 bits con ceros a la izquierda
function decbin32($dec) {
    return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
}

function ip_in_range($ip, $range) {
    if (strpos($range, '/') !== false) {
        list($range, $netmask) = explode('/', $range, 2);
        if (strpos($netmask, '.') !== false) {
            $netmask = str_replace('*', '0', $netmask);
            $netmask_dec = ip2long($netmask);
            return ((ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec));
        } else {
            $x = explode('.', $range);
            while(count($x)<4) $x[] = '0';
            list($a,$b,$c,$d) = $x;
            $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b, empty($c)?'0':$c, empty($d)?'0':$d);
            $range_dec = ip2long($range);
            $ip_dec = ip2long($ip);
            $wildcard_dec = pow(2, (32-$netmask)) - 1;
            $netmask_dec = ~$wildcard_dec;
            return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
        }
    } else {
        if (strpos($range, '*') !== false) {
            $lower = str_replace('*', '0', $range);
            $upper = str_replace('*', '255', $range);
            $range = "$lower-$upper";
        }

        if (strpos($range, '-') !== false) {
            list($lower, $upper) = explode('-', $range, 2);
            $lower_dec = (float)sprintf("%u", ip2long($lower));
            $upper_dec = (float)sprintf("%u", ip2long($upper));
            $ip_dec = (float)sprintf("%u", ip2long($ip));
            return ($ip_dec >= $lower_dec && $ip_dec <= $upper_dec);
        }

        echo 'Formato de rango no válido.';
        return false;
    }
}

function ip_in_ranges($ip, $ranges_array) {
    if (!is_array($ranges_array) || empty($ranges_array)) return false;
    foreach ($ranges_array as $range) {
        if (ip_in_range($ip, $range)) return true;
    }
    return false;
}

/*
* crear_editar_log - Registra eventos en un archivo log personalizado
* Autor: Jaime Jeovanny Cortez Flores
* Versión modificada para adaptabilidad y robustez
*/
function crear_editar_log($ruta_archivo, $contenido, $tipo, $ip, $referer, $useragent) {
    $tipos_log = ["[info]:", "[notice]:", "[warning]:", "[error]:"];
    
    // Genera el formato EXACTO que necesitas:
    $microtime = explode(' ', microtime());
    $fecha = date("m-d-Y H:i:s", $microtime[1]) . substr($microtime[0], 1, 7) . " CST";
    
    $linea_log = "$fecha $ip {$tipos_log[$tipo]} referer: $referer $contenido $useragent" . PHP_EOL;
    
    file_put_contents($ruta_archivo, $linea_log, FILE_APPEND | LOCK_EX);
}
?>