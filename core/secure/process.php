<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $root_path = realpath(__DIR__.'/../..');
    include($root_path.'/core/inc/funciones.inc.php');
    include($root_path.'/core/secure/ips.php');

    $metodo_permitido = "POST";
    $archivo = realpath(__DIR__.'/../../logs/log.log');
    if(!file_exists($archivo)) {
    file_put_contents($archivo, "");
}
    $dominio_autorizado = "localhost";
    $ip = ip_in_ranges($_SERVER["REMOTE_ADDR"], $rango);
    $txt_usuario_autorizado = "admin";
    $txt_password_autorizado = "admin";

    $referer_valido = true; // Cambiar a false en producción
    if (strpos($_SERVER["HTTP_REFERER"] ?? '', $dominio_autorizado) !== false) {
        $referer_valido = true;
    }

    if($referer_valido) {
        if($ip === true) {
            if($_SERVER["REQUEST_METHOD"] == $metodo_permitido) {
                $valor_campo_usuario = (array_key_exists("txt_user", $_POST)) ? htmlspecialchars(stripslashes(trim($_POST["txt_user"])), ENT_QUOTES) : "";
                $valor_campo_password = (array_key_exists("txt_pass", $_POST)) ? htmlspecialchars(stripslashes(trim($_POST["txt_pass"])), ENT_QUOTES) : "";

                if(($valor_campo_usuario != "" || strlen($valor_campo_usuario) > 0) && ($valor_campo_password != "" || strlen($valor_campo_password) > 0)) {
                    $usuario = preg_match('/^[a-zA-Z0-9]{1,10}+$/', $valor_campo_usuario);
                    $password = preg_match('/^[a-zA-Z0-9]{1,10}+$/', $valor_campo_password);

                    if($usuario !== false && $usuario !== 0 && $password !== false && $password !== 0) {
                        if($valor_campo_usuario === $txt_usuario_autorizado && $valor_campo_password === $txt_password_autorizado) {
                            // REGISTRO DE LOG CORREGIDO (con tildes y formato exacto)
      crear_editar_log(
    $archivo,
    "EL CLIENTE INICIÓ SESIÓN SATISFACTORIAMENTE",
    1,
    $_SERVER["REMOTE_ADDR"],
    $_SERVER["HTTP_REFERER"] ?? "DIRECTO",
    $_SERVER["HTTP_USER_AGENT"] ?? "DESCONOCIDO"
);
                            
                            header("Location: ../?status=8");
                            exit();
                        } else {
                            crear_editar_log(
                                $archivo,
                                "CREDENCIALES INCORRECTAS ENVIADAS",
                                2,
                                $_SERVER["REMOTE_ADDR"],
                                $_SERVER["HTTP_REFERER"] ?? "NO_REFERER",
                                $_SERVER["HTTP_USER_AGENT"] ?? "DESCONOCIDO"
                            );
                            header("Location: ../?status=7");
                            exit();
                        }
                    } else {
                        header("Location: ../?status=6");
                        exit();
                    }
                } else {
                    header("Location: ../?status=5");
                    exit();
                }
            } else {
                header("Location: ../?status=4");
                exit();
            }
        } else {
            header("Location: ../?status=3");
            exit();
        }
    } else {
        header("Location: ../?status=2");
        exit();
    }
?>