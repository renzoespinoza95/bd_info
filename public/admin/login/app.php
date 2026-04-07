<?php

Flight::route('GET /api/usuario/mis-datos', function(){

    include DEFINITION;

    global $administrador_actual;

    if(!$administrador_actual){
        echo json_encode(["ok"=>false]);
        return;
    }

    $datos = DB::queryFirstRow("
        SELECT
            u.usu_id,
            u.nombres_apellidos,
            u.email,
            u.sobrenombre,
            u.cod_usu,
            u.img_perfil,
            u.rol_id,
            r.nombre AS rol_nombre,
            r.submenu_inicio,

            nxu.negxusu_id,
            nxu.neg_id,
            nxu.is_activo AS negxusu_activo,
            nxu.fecha_creacion AS negxusu_fecha_creacion,

            n.cod_neg,
            n.descripcion,
            n.nombre AS negocio_nombre,
            n.puesto,
            n.direccion AS negocio_direccion,
            n.ciudad AS negocio_ciudad,
            n.provincia AS negocio_provincia,
            n.departamento AS negocio_departamento,
            n.map_lat AS negocio_lat,
            n.map_lng AS negocio_lng,
            n.img_logo,
            n.is_validado,

            m.mercado_id,
            m.nombre AS mercado_nombre,
            m.direccion AS mercado_direccion,
            m.ciudad AS mercado_ciudad,
            m.provincia AS mercado_provincia,
            m.departamento AS mercado_departamento,
            m.map_lat AS mercado_lat,
            m.map_lng AS mercado_lng

        FROM reg_usu u

        LEFT JOIN reg_rol r
            ON u.rol_id = r.rol_id

        LEFT JOIN reg_negxusu nxu
            ON u.usu_id = nxu.usu_id
            AND nxu.is_activo = 1

        LEFT JOIN reg_neg n
            ON nxu.neg_id = n.neg_id

        LEFT JOIN reg_mercado m
            ON n.mercado_id = m.mercado_id

        WHERE u.usu_id = %i
        LIMIT 1
    ", $administrador_actual['usu_id']);

    echo json_encode([
        "ok"   => true,
        "datos"=> $datos
    ]);

});


Flight::route('POST /coral/login', function () {

    try {

        $data = Flight::request()->data->getData();

        if (!isset($data['sobrenombre']) || !isset($data['clavel'])) {
            Flight::json([
                'status' => 'error',
                'msg'    => 'Payload incompleto'
            ], 400);
            return;
        }

        // 🔐 1️⃣ Desencriptar
        try {
            $sobrenombre = trim(des_barsi($data['sobrenombre']));
            $clavel      = trim(des_barsi($data['clavel']));
        } catch (Exception $e) {
            Flight::json([
                'status' => 'error',
                'msg'    => 'Error al desencriptar credenciales'
            ], 400);
            return;
        }

        if ($sobrenombre === '' || $clavel === '') {
            Flight::json([
                'status' => 'error',
                'msg'    => 'Credenciales vacías'
            ], 400);
            return;
        }

        // 🔎 2️⃣ Validar usuario (solo para login)
        $usuario = DB::queryFirstRow("
            SELECT usu_id
            FROM reg_usu
            WHERE sobrenombre = %s
            AND clavel = %s
            AND (is_activo = 1 OR is_activo IS NULL)
            LIMIT 1
        ", $sobrenombre, $clavel);

        if (!$usuario) {
            Flight::json([
                'status' => 'error',
                'msg'    => 'Credenciales incorrectas'
            ], 401);
            return;
        }

        $usu_id = $usuario['usu_id'];

        // 🧠 3️⃣ Traer TODO (igual que tu query administrador_actual)
        $row = DB::queryFirstRow("
            SELECT
                u.usu_id,
                u.nombres_apellidos,
                u.email,
                u.sobrenombre,
                u.cod_usu,
                u.img_perfil,
                u.rol_id,
                r.nombre AS rol_nombre,
                r.submenu_inicio,

                nxu.negxusu_id,
                nxu.neg_id,
                nxu.is_activo AS negxusu_activo,
                nxu.fecha_creacion AS negxusu_fecha_creacion,

                n.cod_neg,
                n.nombre AS negocio_nombre,
                n.puesto,
                n.direccion AS negocio_direccion,
                n.ciudad AS negocio_ciudad,
                n.provincia AS negocio_provincia,
                n.departamento AS negocio_departamento,
                n.map_lat AS negocio_lat,
                n.map_lng AS negocio_lng,
                n.img_logo,
                n.is_validado,

                m.mercado_id,
                m.nombre AS mercado_nombre,
                m.direccion AS mercado_direccion,
                m.ciudad AS mercado_ciudad,
                m.provincia AS mercado_provincia,
                m.departamento AS mercado_departamento,
                m.map_lat AS mercado_lat,
                m.map_lng AS mercado_lng

            FROM reg_usu u

            LEFT JOIN reg_rol r
                ON u.rol_id = r.rol_id

            LEFT JOIN reg_negxusu nxu
                ON u.usu_id = nxu.usu_id
                AND nxu.is_activo = 1

            LEFT JOIN reg_neg n
                ON nxu.neg_id = n.neg_id

            LEFT JOIN reg_mercado m
                ON n.mercado_id = m.mercado_id

            WHERE u.usu_id = %i
            AND (u.is_activo = 1 OR u.is_activo IS NULL)
            LIMIT 1
        ", $usu_id);

        // 🕒 4️⃣ Último acceso
        DB::update(
            'reg_usu',
            ['fecha_ultimo_acceso' => date('Y-m-d H:i:s')],
            'usu_id=%i',
            $usu_id
        );

        Flight::json([
            'status' => 'ok',
            'data'   => $row
        ], 200, JSON_UNESCAPED_UNICODE);

    } catch (Throwable $e) {

        Flight::json([
            'status' => 'error',
            'msg'    => 'Error interno del servidor'
        ], 500);
    }
});