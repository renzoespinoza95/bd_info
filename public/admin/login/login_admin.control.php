<?php

//==============================
// Vista login
//==============================
// Flight::route('GET /', function() {

//     include DEFINITION;

//     $mBase = $varhost . "/public/admin/login/";
//     // include VARPATH . "/public/admin/login/inicio.php";
//     echo "Pagina de inicio de regentis";

// });


//==============================
// Dashboard administrador
//==============================
Flight::route('GET /admin/dash', function() {

    include DEFINITION;

    global $nombre_app, $apphost, $ssa_id;

    if(empty($ssa_id)){
        Flight::redirect($apphost . "/loginVault");
        exit;
    }

    $valor_key = $nombre_app . vari("KEY");

    $usu_id = perso::decrypt($ssa_id, $valor_key);
    $usu_id = str_replace("*","",$usu_id);

    // obtener info usuario + rol
    $info_admin = DB::queryFirstRow("
        SELECT
            u.usu_id,
            u.nombres_apellidos,
            u.email,
            u.sobrenombre,
            u.rol_id,
            r.submenu_inicio,
            s.url
        FROM reg_usu u
        INNER JOIN reg_rol r
            ON u.rol_id = r.rol_id
        INNER JOIN reg_submenu s
            ON r.submenu_inicio = s.submenu_id
        WHERE u.usu_id = %i
        AND u.is_activo = 1
    ", $usu_id);

    $neg_id_cookie = $_COOKIE["sesion_admin_neg_id_" . $nombre_app] ?? 0;

    $administrador_actual = $info_admin;
    $administrador_actual['neg_id'] = $neg_id_cookie;

    if(!$info_admin){
        Flight::redirect($apphost . "/loginVault");
        exit;
    }

    Flight::redirect($apphost . $info_admin['url']);

});


//==============================
// Vista login
//==============================
Flight::route('GET /loginVault', function() {

    include DEFINITION;

    $mBase = $varhost . "/public/admin/login/";
    include VARPATH . "/public/admin/login/inicio.php";

});


//==============================
// Procesar login
//==============================
Flight::route('POST /loginVault', function() {

    include DEFINITION;

    $json = Flight::request()->getBody();
    $datos_usuario = json_decode($json);

    $usuario = $datos_usuario->usuario ?? '';
    $clavel  = $datos_usuario->clavel ?? '';

    if (empty($usuario) || empty($clavel)) {
        echo perso::error();
        return;
    }

    // verificar usuario
    $info_admin = DB::queryFirstRow("
        SELECT *
        FROM reg_usu
        WHERE sobrenombre = %s
        AND clavel = %s
        AND is_activo = 1
    ", $usuario, $clavel);

    if ($info_admin) {

        global $nombre_app;

        $valor_key = $nombre_app . vari("KEY");

        $sobrenombre = perso::preparar_para_encriptar($usuario);
        $enc_sobrenombre = perso::encrypt($usuario, $valor_key);

        $info_admin['usu_id'] = perso::preparar_para_encriptar($info_admin['usu_id']);
        $enc_info_admin_id = perso::encrypt($info_admin['usu_id'], $valor_key);

        setcookie("ssa_sobrenombre_" . $nombre_app, $enc_sobrenombre, 0, "/");
        setcookie("ssa_id_" . $nombre_app, $enc_info_admin_id, 0, "/");

        // respuesta con rol
        echo json_encode([
            "res"    => "ok",
            "rol_id" => intval($info_admin['rol_id'])
        ]);

    } else {

        echo perso::error();

    }

});


//==============================
// Cerrar sesión
//==============================
Flight::route('GET /finAdmin', function() {

    include DEFINITION;

    global $nombre_app, $apphost;

    setcookie("ssa_sobrenombre_" . $nombre_app, '', time() - 3600, "/");
    setcookie("ssa_id_" . $nombre_app, '', time() - 3600, "/");

    Flight::redirect($apphost . "/loginVault");

});


//==============================
// Ruta test login
//==============================
Flight::route('GET /tt01', function() {

    include DEFINITION;

    $usuario = "renzo";
    $clavel  = "renzo";

    $res = DB::queryFirstRow("
        SELECT COUNT(usu_id) AS cant
        FROM reg_usu
        WHERE sobrenombre = %s
        AND clavel = %s
        AND is_activo = 1
    ", $usuario, $clavel);

    var_dump($res);

});

Flight::route('GET /loginVault/mercados', function(){

    include DEFINITION;

    global $nombre_app;

    $cookie_admin = $_COOKIE["ssa_id_" . $nombre_app] ?? '';

    if(!$cookie_admin){
        echo json_encode([]);
        return;
    }

    $valor_key = $nombre_app . vari("KEY");

    $usu_id = perso::decrypt($cookie_admin, $valor_key);
    $usu_id = str_replace("*","",$usu_id);

    $mercados = DB::query("
        SELECT DISTINCT
            m.mercado_id,
            m.nombre
        FROM reg_mercado m

        INNER JOIN reg_neg n
            ON n.mercado_id = m.mercado_id

        INNER JOIN reg_negxusu nx
            ON nx.neg_id = n.neg_id

        WHERE nx.usu_id = %i
        AND nx.is_activo = 1
        AND n.is_activo = 1
        AND m.is_activo = 1

        ORDER BY m.nombre
    ", $usu_id);

    echo json_encode($mercados);

});

Flight::route('GET /loginVault/negocios/@mercado_id', function($mercado_id){

    include DEFINITION;

    global $nombre_app;

    $cookie_admin = $_COOKIE["ssa_id_" . $nombre_app] ?? '';

    if(!$cookie_admin){
        echo json_encode([]);
        return;
    }

    $valor_key = $nombre_app . vari("KEY");

    $usu_id = perso::decrypt($cookie_admin, $valor_key);
    $usu_id = str_replace("*","",$usu_id);

    $negocios = DB::query("
        SELECT
            n.neg_id,
            n.nombre
        FROM reg_neg n

        INNER JOIN reg_negxusu nx
            ON nx.neg_id = n.neg_id

        WHERE nx.usu_id = %i
        AND n.mercado_id = %i
        AND nx.is_activo = 1
        AND n.is_activo = 1

        ORDER BY n.nombre
    ", $usu_id, $mercado_id);

    echo json_encode($negocios);

});

Flight::route('POST /loginVault/negocioSeleccionado', function(){

    include DEFINITION;

    $json = Flight::request()->getBody();
    $data = json_decode($json,true);

    $neg_id = $data['neg_id'] ?? 0;

    global $nombre_app;

    setcookie(
        "sesion_admin_neg_id_" . $nombre_app,
        $neg_id,
        0,
        "/"
    );

    echo json_encode(["res"=>"ok"]);

});
