<?php

// Subcontroladores modulares (máximo 5 funciones de ruta por archivo)
include_once __DIR__ . '/api/control01.php';
include_once __DIR__ . '/api/control02.php';
include_once __DIR__ . '/api/control03.php';
include_once __DIR__ . '/api/control04.php';
include_once __DIR__ . '/api/control05.php';

// 1. Ruta de la vista principal
Flight::route('GET /', function () {
    include DEFINITION;    

    visita();
    diario("2", 'visita_pag_web', null);
    $mis_visitas = vari("visitas");

    // =========================
    // 🔥 CONSULTA TXT_INICIO DESDE DB
    // =========================
    $inicio = DB::queryFirstRow("
        SELECT titulo, subtitulo_detalle, contenido
        FROM reg_item_pag_web
        WHERE clave_txt = %s
        LIMIT 1
    ", 'TXT_INICIO');

    // =========================
    // 🔥 HEADER DATA
    // =========================
    $header = [
        'titulo' => $inicio['titulo'] ?? 'Regentis - Swipe Cards',        
        'descripcion' => $inicio['subtitulo_detalle'] ?? 'Explora mis proyectos más destacados',
        'logo' => 'https://regentis.app/web/public/ico/favicon.png',
        'icons' => [
            ['url'=>'#','icon'=>'brands fa-twitter','label'=>'Twitter'],
            ['url'=>'#','icon'=>'brands fa-instagram','label'=>'Instagram'],
            ['url'=>'#','icon'=>'brands fa-github','label'=>'Github'],
            ['url'=>'#','icon'=>'fa-envelope','label'=>'Email'],
        ]
    ];

    // =========================
    // 🔥 PROYECTOS DESDE DB
    // =========================
    $imagenes = DB::query("
        SELECT 
            img.pag_item_img_id,
            img.url_img,
            img.titulo AS img_titulo,
            img.descripcion AS img_descripcion,

            i.titulo AS item_titulo,
            i.subtitulo_detalle AS item_descripcion

        FROM reg_pag_item_img img
        INNER JOIN reg_item_pag_web i 
            ON i.item_pag_web_id = img.item_pag_web_id
        ORDER BY img.orden ASC
    ");

    $items = array_map(function($img){
        return [
            'full' => $img['url_img'] ?: 'https://picsum.photos/600/800',
            'thumb' => $img['url_img'] ?: 'https://picsum.photos/600/800',
            'titulo' => $img['img_titulo'] ?: $img['item_titulo'],
            'descripcion' => $img['img_descripcion'] ?: $img['item_descripcion'],
            'position' => 'center center'
        ];
    }, $imagenes);

    // =========================
    // 🔥 DATA GENERAL
    // =========================
    $data = [
        'title'        => $inicio['titulo'] ?? 'Regentis - Swipe Cards',
        'varhost'      => $varhost,
        'apphost'      => $apphost,
        'favicon'      => 'https://regentis.app/web/public/ico/favicon.png',
        'font_size_px' => 20,
        'line_height'  => 1.4,
        'header'       => $header,
        'items'        => $items,
        'visitas'      => $mis_visitas,
        'contenido_inicio' => $inicio['contenido'] ?? '',
        'footer'       => [
            'copyright' => 'Regentis',
            'extra'     => 'Desarrollado con pasión . Visitas ' . $mis_visitas
        ]
    ];

    $basePath = VARPATH . '/public/html/inicio/comp_pag_web';

    $partials = [
        'head'           => file_get_contents($basePath . '/head.html'),
        'navbar_items'   => file_get_contents($basePath . '/navbar_items.html'),
        'thumbnails'     => file_get_contents($basePath . '/thumbnails.html'),
        'item'           => file_get_contents($basePath . '/item.html'),
        'modal_nosotros' => file_get_contents($basePath . '/modal_nosotros.html'),
        'modal_clientes' => file_get_contents($basePath . '/modal_clientes.html'),
        'scripts'        => file_get_contents($basePath . '/scripts.html'),
    ];

    echo (new Mustache)->render(
        file_get_contents(VARPATH . '/public/html/inicio/pag_web.html'),
        $data,
        $partials
    );
});