<?php
Flight::route('GET /', function () {
    global $varhost; // Asegurar acceso a la variable global de configuración
    
    $meta_description = 'HYPRLUV, a loud Y2K hyperpop electronic press kit slideshow template.';
    $meta_keywords = 'hyperpop, y2k, electronic press kit, music, tour';
    $titulo_pag_web = 'HYPRLUV // Electronic Press Kit · TM-627';
    
    // Datos estructurados para las diapositivas y secciones
    $data = [
        'title' => $titulo_pag_web,
        'meta_description' => $meta_description,
        'meta_keywords' => $meta_keywords,
        'base' => $varhost, // URL base absoluta para CSS, JS e imágenes
        
        // Sección 1: Cover
        'cover_kicker' => 'Electronic Press Kit · 2026',
        'cover_title' => 'HYPRLUV',
        'cover_subtitle' => 'Maximum volume hyperpop. Glossy chaos for people who treat the volume knob as a suggestion.',
        
        // Sección 3: Releases
        'releases' => [
            ['titulo' => '1000 Gigabytes Of Luv', 'tipo' => 'Single', 'anio' => '2025'],
            ['titulo' => 'Crash My Heart', 'tipo' => 'Single', 'anio' => '2025'],
            ['titulo' => 'Stardust.exe', 'tipo' => 'EP', 'anio' => '2024'],
        ],
        
        // Sección 4: Stats
        'stats' => [
            ['n' => '2.4M', 'l' => 'Monthly Listeners'],
            ['n' => '180M', 'l' => 'Total Streams'],
            ['n' => '920K', 'l' => 'Followers'],
        ],
        
        // Sección 7: Gallery (con Lorem Picsum)
        'gallery' => [
            ['url_img' => 'https://picsum.photos/seed/hyper1/600/800', 'cap' => 'Press 01'],
            ['url_img' => 'https://picsum.photos/seed/hyper2/600/800', 'cap' => 'Press 02'],
            ['url_img' => 'https://picsum.photos/seed/hyper3/600/800', 'cap' => 'Live 01'],
            ['url_img' => 'https://picsum.photos/seed/hyper4/600/800', 'cap' => 'Live 02'],
        ]
    ];

    // Componentes parciales para Mustache (Todos incluidos y mapeados correctamente)
    $partials = [
        'head'          => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/head.html'),
        'navbar_items'  => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/navbar_items.html'),
        'slides'        => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slides.html'),
        'footerbar'     => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/footerbar.html'),
        'nav_pop'       => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/nav_pop.html'),
        'modal_contacto'=> file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/modal_contacto.html'),
        'thumbnails'    => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/thumbnails.html'),
        'item'          => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/item.html'),
        'scripts'       => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/scripts.html'),
    ];

    // Renderizado con Mustache
    echo (new Mustache())->render(
        file_get_contents(VARPATH . '/public/html/hyper/pag_web.html'),
        $data,
        $partials
    );
});