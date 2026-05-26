<?php

/* =========================================================
   HOME
========================================================= */
Flight::route('GET /', function () {

    include DEFINITION;
    visita();
    diario("2", 'visita_pag_web', null);
    $mis_visitas = vari("visitas");

    // =========================
    // 🔥 INICIO (TXT_INICIO)
    // =========================
    $inicio = DB::queryFirstRow("
        SELECT titulo, subtitulo_detalle, contenido
        FROM reg_item_pag_web
        WHERE clave_txt = %s
        LIMIT 1
    ", 'TXT_INICIO');


    // =========================
    // 🔥 SERVICIOS (TXT_SERVICIOS)
    // =========================
    $servicios_txt = DB::queryFirstRow("
        SELECT titulo, subtitulo_detalle, contenido
        FROM reg_item_pag_web
        WHERE clave_txt = %s
        LIMIT 1
    ", 'TXT_SERVICIOS');

    // =========================
    // 🔥 SLIDER
    // =========================
    $sliders = DB::query("
        SELECT
            slider_id,
            img,
            orden,
            descripcion,
            titulo_superior
        FROM reg_slider
        WHERE neg_id = %i
          AND is_visible = 1
        ORDER BY orden ASC
    ", $pag_web_neg_id);

    // =========================
    // 🔥 SERVICES
    // =========================
    $services = DB::query("
        SELECT i.titulo, i.subtitulo_detalle
        FROM reg_item_pag_web i
        INNER JOIN reg_subcat_pag_web s 
            ON s.subcat_pag_web_id = i.subcat_pag_web_id
        WHERE s.clave_txt = %s
        ORDER BY i.orden ASC
    ", 'TXT_SERVICES');

    // =========================
    // 🔥 FEATURES
    // =========================
    $features = DB::query("
        SELECT i.titulo
        FROM reg_item_pag_web i
        INNER JOIN reg_subcat_pag_web s 
            ON s.subcat_pag_web_id = i.subcat_pag_web_id
        WHERE s.clave_txt = %s
    ", 'TXT_FEATURES');

    // =========================
    // 🔥 SPOTLIGHT
    // =========================
    $spotlight = DB::queryFirstRow("
        SELECT titulo, subtitulo_detalle
        FROM reg_item_pag_web
        WHERE clave_txt = %s
        LIMIT 1
    ", 'TXT_SPOTLIGHT');

    // =========================
    // 🔥 GALERIA
    // =========================
    $gallery = DB::query("
        SELECT url_img
        FROM reg_pag_item_img
        ORDER BY orden ASC
        LIMIT 6
    ");

    // =========================
    // 🔥 NAVBAR
    // =========================
    $rows = DB::query("
        SELECT 
            i.item_pag_web_id, 
            i.titulo, 
            i.url_amigable
        FROM reg_item_pag_web i
        INNER JOIN reg_subcat_pag_web s 
            ON s.subcat_pag_web_id = i.subcat_pag_web_id
        WHERE s.clave_txt = %s
        ORDER BY i.orden ASC, i.item_pag_web_id ASC
        LIMIT 6
    ", 'TXT_TOPNAVBAR');

    $left  = array_slice($rows, 0, 3);
    $right = array_slice($rows, 3, 3);

    // =========================
    // 🔥 DATA BASE
    // =========================
    $data = [
        'logo' => 'assets/main-logo.png',
        'titulo_pag_web' => vari("TITULO_PAG_WEB"),
        'favicon' => $varhost . '/public/ico/favicon.png',
        'base' => $base,
        'visitas' => $mis_visitas,
        'version' => $version,
        'url_proyectos' => $apphost . "/proyectos",        
        'url_inicio' => $apphost,
        'fontawesome_url' => $varhost . '/public/bootstrap/font-awesome/css/font-awesome.css'
    ];

    $data['inicio'] = [
        'titulo' => $inicio['titulo'] ?? 'Bienvenido a Regentis',
        'descripcion' => $inicio['contenido'] ?? ''
    ];

    $data['servicios_txt'] = [
        'titulo' => $servicios_txt['titulo'] ?? 'Se ofrece los servicios',
        'descripcion' => $servicios_txt['contenido'] ?? ''
    ];

    // =========================
    // 🔥 NAV
    // =========================
    $data['nav_left'] = array_map(function($r) use ($apphost){
        return [
            'titulo' => $r['titulo'],
            'url' => $apphost . '/item/' . $r['item_pag_web_id'] . '-' . $r['url_amigable']
        ];
    }, $left);

    $data['nav_right'] = array_map(function($r) use ($apphost){
        return [
            'titulo' => $r['titulo'],
            'url' => $apphost . '/item/' . $r['item_pag_web_id'] . '-' . $r['url_amigable']
        ];
    }, $right);

    // =========================
    // 🔥 SLIDERS
    // =========================
    $data['sliders'] = array_map(function($s){
        return [
            'img' => $s['img'],
            'titulo_superior' => $s['titulo_superior'] ?: 'BIENVENIDO',
            'descripcion' => $s['descripcion'] ?: ''
        ];
    }, $sliders);

    // =========================
    // 🔥 SERVICES
    // =========================
    $data['services'] = array_map(function($s){
        return [
            'titulo' => $s['titulo'],
            'descripcion' => $s['subtitulo_detalle']
        ];
    }, $services);

    // =========================
    // 🔥 FEATURES
    // =========================
    $data['features'] = array_map(function($f){
        return [
            'titulo' => $f['titulo']
        ];
    }, $features);

    // =========================
    // 🔥 SPOTLIGHT
    // =========================
    $data['spotlight'] = [
        'titulo' => $spotlight['titulo'] ?? '',
        'descripcion' => $spotlight['subtitulo_detalle'] ?? ''
    ];

    // =========================
    // 🔥 GALLERY
    // =========================
    $data['gallery'] = array_map(function($g){
        return [
            'url_img' => $g['url_img']
        ];
    }, $gallery);

    // =========================
    // 🔥 PROYECTOS (TXT_PROYECTOS)
    // =========================
    $proyectos = DB::queryFirstRow("
        SELECT item_pag_web_id, titulo, contenido
        FROM reg_item_pag_web
        WHERE clave_txt = %s
        LIMIT 1
    ", 'TXT_PROYECTOS');

    $imagenes_proyectos = [];

    if($proyectos){

        $imagenes_proyectos = DB::query("
            SELECT url_img
            FROM reg_pag_item_img
            WHERE item_pag_web_id = %i
            ORDER BY orden ASC
        ", $proyectos['item_pag_web_id']);
    }

    $data['proyectos'] = [
        'titulo' => $proyectos['titulo'] ?? 'Galería',
        'contenido' => $proyectos['contenido'] ?? '',
        'imagenes' => array_map(function($img){
            return ['url_img' => $img['url_img']];
        }, $imagenes_proyectos)
    ];    

    // =========================
    // 🔥 RENDER
    // =========================
    $partials = [
        'head' => file_get_contents(VARPATH . '/public/html/template/components/head.html'),
        'banner' => file_get_contents(VARPATH . '/public/html/template/components/banner.html'),
        'services' => file_get_contents(VARPATH . '/public/html/template/components/services.html'),
        'features' => file_get_contents(VARPATH . '/public/html/template/components/features.html'),
        'spotlight1' => file_get_contents(VARPATH . '/public/html/template/components/spotlight1.html'),
        'gallery' => file_get_contents(VARPATH . '/public/html/template/components/gallery.html'),
        'contact' => file_get_contents(VARPATH . '/public/html/template/components/contact.html'),
        'footer' => file_get_contents(VARPATH . '/public/html/template/components/footer.html'),
        'scripts' => file_get_contents(VARPATH . '/public/html/template/components/scripts.html'),
    ];

    echo (new Mustache)->render(
        file_get_contents(VARPATH . '/public/html/template/inicio.html'),
        $data,
        $partials
    );
});


/* =========================================================
   ITEM DETALLE
========================================================= */
Flight::route('GET /item/@slug', function ($slug) {

    include DEFINITION;    

    // =========================
    // 🔥 EXTRAER ID
    // =========================
    $partes = explode('-', $slug);
    $id = intval($partes[0]);

    if($id <= 0){
        echo "URL inválida";
        return;
    }

    // =========================
    // 🔥 ITEM
    // =========================
    $item = DB::queryFirstRow("
        SELECT titulo, contenido, subtitulo_detalle
        FROM reg_item_pag_web
        WHERE item_pag_web_id = %i
    ", $id);

    if(!$item){
        echo "Item no encontrado";
        return;
    }

    // =========================
    // 🔥 IMAGENES
    // =========================
    $imagenes = DB::query("
        SELECT url_img
        FROM reg_pag_item_img
        WHERE item_pag_web_id = %i
        ORDER BY orden ASC
    ", $id);

    // =========================
    // 🔥 LOGOTIPOS
    // =========================
    $logotipos = DB::query("
        SELECT img.url_img
        FROM reg_item_pag_web i
        INNER JOIN reg_pag_item_img img 
            ON img.item_pag_web_id = i.item_pag_web_id
        WHERE i.clave_txt = %s
        ORDER BY img.orden ASC
    ", 'TXT_LOGOTIPOS');

    // =========================
    // 🔥 DATA BASE
    // =========================
    $data = [
        'logo' => 'assets/main-logo.png',
        'title' => $item['titulo'],
        'favicon' => $varhost . '/public/ico/favicon.png',
        'base' => $base,
        'url_inicio' => $apphost,
        'fontawesome_url' => $varhost . '/public/bootstrap/font-awesome/css/font-awesome.css'
    ];

    // =========================
    // 🔥 ITEM DATA
    // =========================
    $data['titulo'] = $item['titulo'];
    $data['contenido'] = $item['contenido'];

    $texto = trim(strip_tags($item['subtitulo_detalle'] ?? ''));

    if($texto === ''){
        $data['contenido_resumen'] = '';
    }else{
        $palabras = preg_split('/\s+/', $texto);
        $data['contenido_resumen'] = implode(' ', array_slice($palabras, 0, 40));
    }

    $data['imagen_principal'] = isset($imagenes[0]) 
        ? $imagenes[0]['url_img'] 
        : 'https://picsum.photos/1200/500';




    // =========================
    // 🔥 RENDER
    // =========================
    $partials = [
        'head' => file_get_contents(VARPATH . '/public/html/template/components/head.html'),
        'footer' => file_get_contents(VARPATH . '/public/html/template/components/footer.html'),
        'scripts' => file_get_contents(VARPATH . '/public/html/template/components/scripts.html')
    ];

    echo (new Mustache)->render(
        file_get_contents(VARPATH . '/public/html/template/detalle.html'),
        $data,
        $partials
    );
});

/* =========================================================
   PROYECTOS
========================================================= */
Flight::route('GET /proyectos', function () {

    include DEFINITION;

    visita("visita_portafolio");
    diario("2", 'visita_portafolio', null);

    // =========================
    // 🔥 HEADER DATA
    // =========================
    $header = [
        'titulo' => 'Portafolio Regentis',        
        'descripcion' => 'Explora nuestros proyectos más destacados',
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
            'full' => $img['url_img'],
            'thumb' => $img['url_img'],

            // 🔥 USA PRIMERO IMG
            'titulo' => $img['img_titulo'] ?: $img['item_titulo'],

            'descripcion' => $img['img_descripcion'] ?: $img['item_descripcion'],

            'position' => 'center center'
        ];

    }, $imagenes);

    // =========================
    // 🔥 DATA GENERAL
    // =========================
    $data = [
        'title' => 'Proyectos | Regentis',
        'favicon' => $varhost . '/public/ico/favicon.png',
        'base' => $base ?? '',
        'header' => $header,

        // 🔥 META TAGS DINÁMICOS
        'meta_keywords' => vari("meta_keywords"),
        'meta_description' => vari("meta_description"),
        'meta_author' => vari("meta_author"),


        'items' => $items,
        'footer' => [
            'copyright' => 'Regentis',
            'extra' => 'Desarrollado con pasión . Visitas ' . vari("visita_portafolio") 
        ]
    ];

    // =========================
    // 🔥 PARTIALS
    // =========================
    $partials = [
        'head' => file_get_contents(VARPATH . '/public/html/proyectos/comp_proyectos/head.html'),
        'header' => file_get_contents(VARPATH . '/public/html/proyectos/comp_proyectos/header.html'),
        'thumbnails' => file_get_contents(VARPATH . '/public/html/proyectos/comp_proyectos/thumbnails.html'),
        'item' => file_get_contents(VARPATH . '/public/html/proyectos/comp_proyectos/item.html'),
        'footer' => file_get_contents(VARPATH . '/public/html/proyectos/comp_proyectos/footer.html'),
        'scripts' => file_get_contents(VARPATH . '/public/html/proyectos/comp_proyectos/scripts.html'),
    ];

    // =========================
    // 🔥 RENDER
    // =========================
    echo (new Mustache)->render(
        file_get_contents(VARPATH . '/public/html/proyectos/inicio.html'),
        $data,
        $partials
    );
});