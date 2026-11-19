<?php

include_once __DIR__ . '/api/control01.php';

Flight::route('GET /', function () {
    global $varhost, $apphost, $neg_id; // neg_id global del negocio actual

    perso::visita();
    $mis_visitas = perso::vari("visitas");

    // Helper: devuelve un item de texto (clave_txt) SOLO del negocio actual
    // (reg_item_pag_web -> reg_subcat_pag_web -> reg_cat_pag_web.neg_id)
    $txt_neg = function ($clave) use ($neg_id) {
        return DB::queryFirstRow("
            SELECT i.item_pag_web_id, i.titulo, i.subtitulo_detalle, i.contenido
            FROM reg_item_pag_web i
            INNER JOIN reg_subcat_pag_web s ON s.subcat_pag_web_id = i.subcat_pag_web_id
            INNER JOIN reg_cat_pag_web c ON c.cat_pag_web_id = s.cat_pag_web_id
            WHERE i.clave_txt = %s AND c.neg_id = %i
            ORDER BY i.item_pag_web_id ASC
            LIMIT 1
        ", $clave, $neg_id);
    };

    // Helper: item de texto dentro de una subcategoría concreta (por clave_txt) del negocio actual
    $txt_neg_subcat = function ($subcat_clave, $clave) use ($neg_id) {
        return DB::queryFirstRow("
            SELECT i.contenido
            FROM reg_item_pag_web i
            INNER JOIN reg_subcat_pag_web s ON s.subcat_pag_web_id = i.subcat_pag_web_id
            INNER JOIN reg_cat_pag_web c ON c.cat_pag_web_id = s.cat_pag_web_id
            WHERE s.clave_txt = %s AND i.clave_txt = %s AND c.neg_id = %i
            ORDER BY i.item_pag_web_id ASC
            LIMIT 1
        ", $subcat_clave, $clave, $neg_id);
    };

    // Helper: convierte el codigo_web de un video en la URL de embed (Vimeo / YouTube / URL directa)
    $video_src = function ($raw) {
        $raw = trim((string)$raw);
        if ($raw === '') return '';
        if (stripos($raw, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $raw, $m)) {
            $raw = $m[1];
        }
        if (preg_match('/^\d+$/', $raw)) {
            return 'https://player.vimeo.com/video/' . $raw . '?autoplay=1';
        }
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $raw, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1] . '?autoplay=1';
        }
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~i', $raw, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?autoplay=1';
        }
        if (preg_match('~^https?://~i', $raw)) {
            return $raw;
        }
        if (preg_match('/(\d+)/', $raw, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1] . '?autoplay=1';
        }
        return '';
    };

    $inicio = $txt_neg('txt_inicio');

    // txt_nosotros del negocio actual
    $nosotros_row = $txt_neg('txt_nosotros');
    $nosotros_item_id = $nosotros_row['item_pag_web_id'] ?? 0;

    // 1ra imagen visible del item txt_nosotros del negocio actual
    $nosotros_img_row = DB::queryFirstRow("
        SELECT url_img 
        FROM reg_pag_item_img
        WHERE item_pag_web_id = %i AND is_visible = 1
        ORDER BY orden ASC, pag_item_img_id ASC
        LIMIT 1
    ", $nosotros_item_id);

    $nosotros_imagen = $nosotros_img_row['url_img'] ?? 'https://picsum.photos/600/800';

    $nosotros_videos = DB::query("
        SELECT pag_item_vid_id, titulo AS video_titulo, codigo_web, url_img
        FROM reg_pag_item_vid
        WHERE item_pag_web_id = %i AND is_visible = 1
        ORDER BY orden ASC
    ", $nosotros_item_id);

    $tiene_videos_nosotros = !empty($nosotros_videos);
    $raw_codigo_web = $tiene_videos_nosotros ? $nosotros_videos[0]['codigo_web'] : '';

    $nosotros_video_id = '';
    if (!empty($raw_codigo_web)) {
        if (preg_match('/(\d+)/', $raw_codigo_web, $matches)) {
            $nosotros_video_id = $matches[1];
        } else {
            $nosotros_video_id = $raw_codigo_web;
        }
    }

    // Enlaces dinámicos de TikTok y Facebook (filtrados por neg_id)
    $tiktok_row = $txt_neg('txt_tiktok');
    $facebook_row = $txt_neg('txt_facebook');

    $url_tiktok = $tiktok_row['contenido'] ?? 'https://www.tiktok.com';
    $url_facebook = $facebook_row['contenido'] ?? 'https://www.facebook.com';

    // Meta tags (filtrados por neg_id)
    $meta_keywords_row = $txt_neg_subcat('txt_meta', 'txt_meta_keywords');
    $meta_desc_row = $txt_neg_subcat('txt_meta', 'txt_meta_description');

    $meta_description = $meta_desc_row['contenido'] ?? 'mi meta_description';
    $meta_keywords = $meta_keywords_row['contenido'] ?? 'mi meta_keywords';

    $nosotros_titulo = $nosotros_row['titulo'] ?? 'NOSOTROS';
    $nosotros_contenido = $nosotros_row['contenido'] ?? 'Información institucional de la empresa.';

    // Títulos de la sección de promociones (filtrados por neg_id)
    $promos_sub_row = $txt_neg('txt_subtitulo_promocion');
    $promos_tit_row = $txt_neg('txt_titulo_promocion');

    $subtitulo_promocion = $promos_sub_row['contenido'] ?? 'Catálogo & Servicios';
    $titulo_promocion = $promos_tit_row['contenido'] ?? 'NUEVOS MODELOS';

    // "MIS RECIENTES TRABAJOS" -> "NUEVOS MODELOS" (aunque el texto venga de la BD)
    $subtitulo_promocion = str_ireplace('mis recientes trabajos', 'NUEVOS MODELOS', $subtitulo_promocion);
    $titulo_promocion = str_ireplace('mis recientes trabajos', 'NUEVOS MODELOS', $titulo_promocion);

    // Datos del negocio actual mediante el $neg_id global
    $negocio = DB::queryFirstRow("SELECT neg_id, nombre, img_logo, celular_informes FROM reg_neg WHERE neg_id = %i LIMIT 1", $neg_id);
    $nombre_negocio = $negocio['nombre'] ?? 'mi nombre_negocio';
    $img_logo = $negocio['img_logo'] ?: 'https://barsi-img.b-cdn.net/recursos/logo-regentis.png';
    $celular_informes = $negocio['celular_informes'] ?? 'mi celular_informes';

    $titulo_pag_web = $nombre_negocio;

    $celular_limpio = preg_replace('/[^0-9]/', '', $celular_informes);
    if (strlen($celular_limpio) === 9) {
        $celular_limpio = '51' . $celular_limpio;
    }
    $url_whatsapp = 'https://wa.me/' . $celular_limpio . '?text=' . urlencode("Estimados, me comunico a través de su página web para solicitar mayor información.");

    $anio_actual = date('Y');
    $footer_text = $nombre_negocio . ' ' . $anio_actual . ' · Hecho por Regentis · Visitas: ' . $mis_visitas;

    // Sliders filtrados por el neg_id global
    $sliders_raw = DB::query("
        SELECT slider_id, img, orden, descripcion, titulo_superior
        FROM reg_slider
        WHERE neg_id = %i AND is_visible = 1
        ORDER BY orden ASC
    ", $neg_id);

    $sliders = array_map(function($s) {
        return [
            'img' => $s['img'] ?: 'https://picsum.photos/1200/600',
            'titulo_superior' => $s['titulo_superior'] ?: 'mi titulo_superior',
            'descripcion' => $s['descripcion'] ?: 'mi descripcion'
        ];
    }, $sliders_raw);

    if (empty($sliders)) {
        $sliders = [
            [
                'img' => 'https://picsum.photos/seed/slide1/1200/600',
                'titulo_superior' => 'mi titulo_superior',
                'descripcion' => 'mi descripcion'
            ]
        ];
    }

    // Categoría de diseños filtrada por el neg_id global
    $cat_disenos = DB::queryFirstRow("
        SELECT cat_pag_web_id 
        FROM reg_cat_pag_web 
        WHERE clave_txt = %s AND neg_id = %i
        LIMIT 1
    ", 'txt_disenos', $neg_id);

    $promociones = [];
    $subcat_detalle_activa = [
        'titulo' => 'Detalle de Servicio',
        'texto02' => 'Información detallada de la subcategoría seleccionada.',
        'items' => []
    ];

    if ($cat_disenos) {
        // Se excluye la subcategoría txt_sistemas: tiene su propia pantalla "Sistemas"
        $subcats_raw = DB::query("
            SELECT subcat_pag_web_id, titulo, url_img, texto01, texto02
            FROM reg_subcat_pag_web
            WHERE cat_pag_web_id = %i AND is_visible = 1
              AND (clave_txt IS NULL OR clave_txt != %s)
            ORDER BY orden ASC, subcat_pag_web_id ASC
        ", $cat_disenos['cat_pag_web_id'], 'txt_sistemas');

        $idx = 0;
        foreach ($subcats_raw as $sc) {
            $subcat_id = $sc['subcat_pag_web_id'];

            $items_raw = DB::query("
                SELECT i.item_pag_web_id, i.titulo AS item_titulo, i.contenido, img.url_img AS item_url_img
                FROM reg_item_pag_web i
                INNER JOIN reg_pag_item_img img ON img.item_pag_web_id = i.item_pag_web_id
                WHERE i.subcat_pag_web_id = %i AND i.is_visible = 1 AND img.is_visible = 1
                ORDER BY i.orden ASC, img.orden ASC
            ", $subcat_id);

            $galeria_items = [];
            foreach ($items_raw as $it) {
                $item_id = $it['item_pag_web_id'];

                $videos_raw = DB::query("
                    SELECT pag_item_vid_id, titulo AS video_titulo, codigo_web, url_img
                    FROM reg_pag_item_vid
                    WHERE item_pag_web_id = %i AND is_visible = 1
                    ORDER BY orden ASC
                ", $item_id);

                $videos_item = [];
                foreach($videos_raw as $vid) {
                    $videos_item[] = [
                        'titulo' => $vid['video_titulo'] ?? 'VISUALIZAR',
                        'codigo_web' => $vid['codigo_web']
                    ];
                }

                $galeria_items[] = [
                    'titulo' => $it['item_titulo'] ?? $sc['titulo'],
                    'img' => $it['item_url_img'] ?: ($sc['url_img'] ?: 'https://picsum.photos/seed/item/600/800'),
                    'videos' => $videos_item,
                    'tiene_videos' => !empty($videos_item)
                ];
            }

            if (empty($galeria_items)) {
                $galeria_items[] = [
                    'titulo' => $sc['titulo'],
                    'img' => $sc['url_img'] ?: 'https://picsum.photos/seed/defaultsub/600/800',
                    'videos' => [],
                    'tiene_videos' => false
                ];
            }

            if ($idx === 0) {
                $subcat_detalle_activa = [
                    'titulo' => $sc['titulo'],
                    'texto02' => $sc['texto02'],
                    'items' => $galeria_items
                ];
            }

            $promociones[] = [
                'titulo' => $sc['titulo'] ?? 'SERVICIO',
                'img' => $sc['url_img'] ?: 'https://picsum.photos/seed/serv/600/450',
                'url' => '#',
                'slide_target' => 9,
                'html_contenido' => $sc['texto02'] ?? '',
                'subcat_items' => $galeria_items,
                'subcat_items_json_encoded' => json_encode($galeria_items, JSON_UNESCAPED_UNICODE)
            ];
            $idx++;
        }
    }

    if (empty($promociones)) {
        $galeria_default = [['titulo' => 'Ingeniería', 'img' => 'https://picsum.photos/seed/i1/600/800', 'videos' => [], 'tiene_videos' => false]];
        $promociones = [
            [
                'titulo' => 'SERVICIOS',
                'img' => 'https://picsum.photos/seed/serv1/600/450',
                'url' => '#',
                'slide_target' => 9,
                'html_contenido' => '<span>Servicio de ingeniería y alquiler de maquinarias.</span>',
                'subcat_items' => $galeria_default,
                'subcat_items_json_encoded' => json_encode($galeria_default, JSON_UNESCAPED_UNICODE)
            ]
        ];
        $subcat_detalle_activa = [
            'titulo' => 'SERVICIOS',
            'texto02' => 'Servicio de ingeniería y alquiler de maquinarias.',
            'items' => $galeria_default
        ];
    }

    // ==========================================
    // SISTEMAS: subcategoría con clave_txt = txt_sistemas (dentro de la categoría
    // del negocio actual, ej. txt_disenos). Por cada item: 1ra imagen visible
    // (miniatura) y 1er video visible (modal).
    // ==========================================
    $sistemas = [];

    $sis_items = DB::query("
        SELECT i.item_pag_web_id, i.titulo, s.url_img AS subcat_img
        FROM reg_item_pag_web i
        INNER JOIN reg_subcat_pag_web s ON s.subcat_pag_web_id = i.subcat_pag_web_id
        INNER JOIN reg_cat_pag_web c ON c.cat_pag_web_id = s.cat_pag_web_id
        WHERE s.clave_txt = %s AND c.neg_id = %i AND s.is_visible = 1 AND i.is_visible = 1
        ORDER BY i.orden ASC, i.item_pag_web_id ASC
    ", 'txt_sistemas', $neg_id);

    foreach ($sis_items as $si) {
        $img_row = DB::queryFirstRow("
            SELECT url_img
            FROM reg_pag_item_img
            WHERE item_pag_web_id = %i AND is_visible = 1
            ORDER BY orden ASC, pag_item_img_id ASC
            LIMIT 1
        ", $si['item_pag_web_id']);

        $vid_row = DB::queryFirstRow("
            SELECT codigo_web
            FROM reg_pag_item_vid
            WHERE item_pag_web_id = %i AND is_visible = 1
            ORDER BY orden ASC, pag_item_vid_id ASC
            LIMIT 1
        ", $si['item_pag_web_id']);

        $src = $video_src($vid_row['codigo_web'] ?? '');

        $sistemas[] = [
            'titulo'      => $si['titulo'] ?: 'SISTEMA',
            'img'         => ($img_row['url_img'] ?? '') ?: ($si['subcat_img'] ?: 'https://picsum.photos/seed/sistema/600/450'),
            'video_src'   => $src,
            'tiene_video' => ($src !== '')
        ];
    }

    // Marquesina: SOLO items del negocio actual (reg_cat_pag_web.neg_id = $neg_id)
    $marquesina_items = DB::query("
        SELECT i.contenido 
        FROM reg_item_pag_web i
        INNER JOIN reg_subcat_pag_web s ON s.subcat_pag_web_id = i.subcat_pag_web_id
        INNER JOIN reg_cat_pag_web c ON c.cat_pag_web_id = s.cat_pag_web_id
        WHERE s.clave_txt = %s AND i.is_visible = 1 AND c.neg_id = %i
        ORDER BY i.orden ASC, i.item_pag_web_id ASC
    ", 'txt_marquesina', $neg_id);

    $marquesina = [];
    if (!empty($marquesina_items)) {
        foreach ($marquesina_items as $m) {
            if (!empty($m['contenido'])) {
                $marquesina[] = $m['contenido'];
            }
        }
    }
    if (empty($marquesina)) {
        // Sin textos propios: se muestra el nombre del negocio (nunca datos de otro negocio)
        $marquesina = [$nombre_negocio];
    }

    $data = [
        'title'                   => $titulo_pag_web,
        'nombre_negocio'          => $nombre_negocio,
        'img_logo'                => $img_logo,
        'celular_informes'        => $celular_informes,
        'url_whatsapp'            => $url_whatsapp,
        'url_tiktok'              => $url_tiktok,
        'url_facebook'            => $url_facebook,
        'footer_text'             => $footer_text,
        'subtitulo_promocion'     => $subtitulo_promocion,
        'titulo_promocion'        => $titulo_promocion,
        'nosotros_titulo'         => $nosotros_titulo,
        'nosotros_contenido'      => $nosotros_contenido,
        'nosotros_imagen'         => $nosotros_imagen,
        'tiene_videos_nosotros'   => $tiene_videos_nosotros,
        'nosotros_video_codigo'   => $nosotros_video_id,
        'subcat_titulo'           => $subcat_detalle_activa['titulo'],
        'subcat_texto02'          => $subcat_detalle_activa['texto02'],
        'subcat_items'            => $subcat_detalle_activa['items'],
        'meta_description'        => $meta_description,
        'meta_keywords'           => $meta_keywords,
        'base'                    => $varhost, 
        'sliders'                 => $sliders,
        'promociones'             => $promociones,
        'sistemas'                => $sistemas,
        'marquesina_words'        => $marquesina,
        'releases'                => [['titulo' => '1000 Gigabytes Of Luv', 'tipo' => 'Single', 'anio' => '2025']],
        'stats'                   => [['n' => '2.4M', 'l' => 'Monthly Listeners']],
        'gallery'                 => []
    ];

    $partials = [
        'head'                => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/head.html'),
        'navbar_items'        => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/navbar_items.html'),
        'slides'              => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slides.html'),
        'slide_01_cover'      => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_01_cover.html'),
        'promociones'         => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/promociones.html'),
        'slide_02_bio'        => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_02_bio.html'),
        'slide_03_music'      => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_03_music.html'),
        'slide_04_stats'      => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_04_stats.html'),
        'slide_05_press'      => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_05_press.html'),
        'slide_06_tour'       => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_06_tour.html'),
        'slide_07_visuals'    => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_07_visuals.html'),
        'slide_08_contact'    => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_08_contact.html'),
        'slide_09_templatemo' => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_09_templatemo.html'),
        'slide_10_subcategoria' => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_10_subcategoria.html'),
        'slide_11_sistemas'   => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/slide_11_sistemas.html'),
        'navpop_02_menusubcat' => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/navpop_02_menusubcat.html'),
        'footerbar'           => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/footerbar.html'),
        'nav_pop'             => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/nav_pop.html'),
        'modal_contacto'      => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/modal_contacto.html'),
        'thumbnails'          => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/thumbnails.html'),
        'item'                => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/item.html'),
        'scripts'             => file_get_contents(VARPATH . '/public/html/hyper/comp_pag_web/scripts.html'),
    ];

    echo (new Mustache)->render(
        file_get_contents(VARPATH . '/public/html/hyper/pag_web.html'),
        $data,
        $partials
    );
});