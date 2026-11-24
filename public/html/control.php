<?php
// =============================================================
// GCGX1c  -  control.php  (FlightPHP + Mustache + MeekroDB)
// Mismas claves txt_ que el control.php de ejemplo.
// =============================================================

if (!function_exists('gc_embed')) {

    function gc_embed($u) {
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $u, $m)) return 'https://player.vimeo.com/video/' . $m[1];
        if (preg_match('~(?:v=|youtu\.be/|embed/)([\w-]{11})~', $u, $m)) return 'https://www.youtube.com/embed/' . $m[1];
        return $u;
    }

    function gc_img($u, $seed, $w = 800, $h = 600) {
        return !empty($u) ? $u : "https://picsum.photos/seed/$seed/$w/$h";
    }

    // Normaliza un item de la web para Mustache
    function gc_item($it) {
        $im   = isset($it['imagenes']) ? $it['imagenes'] : array();
        $vids = array();
        foreach ((isset($it['videos']) ? $it['videos'] : array()) as $v) {
            if (empty($v['codigo_web'])) continue;
            $vids[] = array(
                'titulo' => $v['titulo'],
                'embed'  => gc_embed($v['codigo_web']),
                'thumb'  => gc_img(isset($v['url_img']) ? $v['url_img'] : '', 'vid' . $v['pag_item_vid_id'], 200, 150),
                'grupo'  => $it['titulo'],
            );
        }
        $id = $it['item_pag_web_id'];
        return array(
            'id'        => $id,
            'clave'     => $it['clave_txt'],
            'titulo'    => $it['titulo'],
            'contenido' => $it['contenido'],
            'subtitulo' => isset($it['subtitulo_detalle']) ? $it['subtitulo_detalle'] : '',
            'img'       => isset($im[0]['url_img']) ? $im[0]['url_img'] : '',
            'img2'      => isset($im[1]['url_img']) ? $im[1]['url_img'] : '',
            'imgx'      => gc_img(isset($im[0]['url_img']) ? $im[0]['url_img'] : '', 'it' . $id),
            'videos'    => $vids,
        );
    }
}

Flight::route('GET /', function () {
    global $neg_id;

    include DEFINITION;

    // Suma una visita para el negocio actual
    perso::visita();

    $vista   = VARPATH . '/public/html';
    $base    = isset($varhost) ? $varhost . '/public/html' : '';
    $version = isset($version) ? $version : date('Ymd');

    // ===== Datos del negocio (reg_neg) =====
    $negocio = DB::queryFirstRow("
        SELECT neg_id, nombre, img_logo, celular_informes
        FROM reg_neg
        WHERE neg_id = %i
        LIMIT 1
    ", $neg_id);

    $nombre_negocio   = !empty($negocio['nombre'])          ? $negocio['nombre']          : 'GCGX1c';
    $img_logo         = !empty($negocio['img_logo'])        ? $negocio['img_logo']        : 'https://picsum.photos/seed/gcgx1c-logo/64/64';
    $celular_informes = isset($negocio['celular_informes']) ? $negocio['celular_informes'] : '';

    $mis_visitas = perso::obtener_visitas();

    // Arbol completo: negocio, web, pos y sliders
    $data = perso::regentis_rss($neg_id);

    // ===== Indices de la estructura web =====
    $cats = array();   // clave cat  => fila categoria
    $its  = array();   // clave cat  => clave sub => [items normalizados]
    foreach ((isset($data['web']) ? $data['web'] : array()) as $c) {
        $cats[$c['clave_txt']] = $c;
        foreach ((isset($c['subcategorias']) ? $c['subcategorias'] : array()) as $s) {
            $its[$c['clave_txt']][$s['clave_txt']] = array_map('gc_item', isset($s['items']) ? $s['items'] : array());
        }
    }
    $g = function ($c, $s) use ($its) {
        return isset($its[$c][$s]) ? $its[$c][$s] : array();
    };
    $one = function ($c, $s, $k) use ($g) {
        foreach ($g($c, $s) as $x) { if ($x['clave'] === $k) return $x; }
        return array('id' => 0, 'titulo' => '', 'contenido' => '', 'subtitulo' => '', 'img' => '', 'img2' => '', 'imgx' => '', 'videos' => array());
    };
    $ct = function ($c, $campo, $def = '') use ($cats) {
        return !empty($cats[$c][$campo]) ? $cats[$c][$campo] : $def;
    };

    // ===== Metadatos (txt_meta_cat / txt_meta) =====
    $meta = array();
    foreach ($g('txt_meta_cat', 'txt_meta') as $x) { $meta[$x['clave']] = $x; }
    $mv = function ($k, $def = '') use ($meta) {
        return !empty($meta[$k]['contenido']) ? $meta[$k]['contenido'] : $def;
    };

    $favicon_url = $img_logo;
    foreach (array('txt_dueno_restaurante', 'txt_dueno_negocio') as $k) {
        if (!empty($meta[$k]['img'])) { $favicon_url = $meta[$k]['img']; break; }
    }

    // ===== Tema (txt_selector_tema o ?tema=N) =====
    $temas = array(
        1  => array('01_solar_pop', 'Solar Pop'),        2  => array('02_luxury_gold', 'Luxury Gold'),
        3  => array('03_ocean_fresh', 'Ocean Fresh'),    4  => array('04_purple_dream', 'Purple Dream'),
        5  => array('05_natural_green', 'Natural Green'), 6  => array('06_urban_black', 'Urban Black'),
        7  => array('07_pink_bloom', 'Pink Bloom'),      8  => array('08_tropical_sun', 'Tropical Sun'),
        9  => array('09_royal_blue', 'Royal Blue'),      10 => array('10_earth_terracotta', 'Earth Terracotta'),
        11 => array('11_neon_energy', 'Neon Energy'),    12 => array('12_chocolate_cream', 'Chocolate Cream'),
    );
    $tema_num = isset($_GET['tema']) ? (int) $_GET['tema'] : (int) $mv('txt_selector_tema', 1);
    if (!isset($temas[$tema_num])) $tema_num = 1;
    $temas_list = array();
    foreach ($temas as $n => $t) {
        $temas_list[] = array('num' => $n, 'nombre' => $t[1], 'sel' => ($n === $tema_num) ? 'selected' : '');
    }

    // ===== Sliders =====
    $sliders = array();
    $hoy = date('Y-m-d H:i:s');
    foreach ((isset($data['sliders']) ? $data['sliders'] : array()) as $sl) {
        if (isset($sl['is_visible']) && $sl['is_visible'] === 0) continue;
        if (!empty($sl['fecha_fin']) && $sl['fecha_fin'] < $hoy) continue;
        $sliders[] = array(
            'img'             => gc_img($sl['img'], 'sl' . $sl['slider_id'], 1920, 900),
            'titulo_superior' => isset($sl['titulo_superior']) ? $sl['titulo_superior'] : '',
            'descripcion'     => isset($sl['descripcion']) ? $sl['descripcion'] : '',
            'orden'           => $sl['orden'],
        );
    }
    if (!$sliders) {
        $sliders[] = array('img' => gc_img($ct('txt_inicio', 'url_img'), 'hero', 1920, 900), 'titulo_superior' => $ct('txt_inicio', 'texto01', $nombre_negocio), 'descripcion' => $ct('txt_inicio', 'texto02'), 'orden' => 1);
    }
    foreach ($sliders as $i => $s) {
        $sliders[$i]['primero'] = ($i === 0) ? 1 : 0;
        $sliders[$i]['n']       = $i;
    }

    // ===== Propuesta / marquesina =====
    $propuesta = array();
    foreach ($g('txt_inicio', 'txt_propuesta') as $i => $x) {
        $x['num'] = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
        $propuesta[] = $x;
    }

    // ===== Empresa =====
    $hist  = $one('txt_empresa', 'txt_historia', 'txt_historia_titulo');
    $hitosI = $one('txt_empresa', 'txt_historia', 'txt_historia_hitos');
    $hitos = array();
    foreach (array_filter(array_map('trim', explode('·', (string) $hitosI['contenido']))) as $h) {
        if (preg_match('/^(\d{4})\s+(.*)$/u', $h, $m)) $hitos[] = array('anio' => $m[1], 'texto' => $m[2]);
        else $hitos[] = array('anio' => '*', 'texto' => $h);
    }

    // ===== Servicios + videos =====
    $videos = array();
    foreach ($g('txt_servicios', 'txt_video') as $x) {
        foreach ($x['videos'] as $v) $videos[] = $v;
    }

    // ===== Contacto =====
    $tel      = $one('txt_contacto', 'txt_datos_contacto', 'txt_telefono');
    $tel_dig  = preg_replace('/\D+/', '', $tel['contenido']);
    $wa       = preg_replace('/\D+/', '', $celular_informes);
    if (strlen($wa) === 9) $wa = '51' . $wa;
    if ($wa === '') $wa = $tel_dig;
    $faq = array();
    foreach ($g('txt_contacto', 'txt_faq') as $i => $x) {
        $x['abierto'] = ($i === 0) ? 'show' : '';
        $x['colapsado'] = ($i === 0) ? '' : 'collapsed';
        $faq[] = $x;
    }
    $mapa = $one('txt_contacto', 'txt_datos_contacto', 'txt_mapa');

    // ===== Productos POS =====
    $prods = array(); $cats_pos = array(); $js_prods = array();
    foreach ((isset($data['pos']) ? $data['pos'] : array()) as $pc) {
        if (isset($pc['is_activo']) && $pc['is_activo'] === 0) continue;
        $cid = $pc['category_id'];
        $n = 0;
        foreach ($pc['productos'] as $p) {
            if (isset($p['is_visible']) && $p['is_visible'] === 0) continue;
            $pid = $p['product_id'];
            $n++;
            if (isset($prods[$pid])) { $prods[$pid]['cats'] .= ' ' . $cid; continue; }

            $img = isset($p['imagenes'][0]['img']) ? $p['imagenes'][0]['img'] : "https://picsum.photos/seed/p$pid/800/800";
            $attr = ''; $ops = array();
            if (isset($p['variantes_json']) && is_array($p['variantes_json'])) {
                $attr = isset($p['variantes_json']['atributo']) ? $p['variantes_json']['atributo'] : '';
                foreach ((isset($p['variantes_json']['opciones']) ? $p['variantes_json']['opciones'] : array()) as $o) {
                    if (!empty($o['valor'])) $ops[] = $o['valor'];
                }
            }
            $prods[$pid] = array(
                'id' => $pid, 'name' => $p['name'], 'marca' => isset($p['marca_des']) ? $p['marca_des'] : '',
                'precio_fmt' => number_format((float) $p['price'], 2), 'img' => $img, 'cats' => (string) $cid,
            );
            $js_prods[] = array(
                'id' => $pid, 'name' => $p['name'], 'marca' => isset($p['marca_des']) ? $p['marca_des'] : '',
                'price' => (float) $p['price'], 'img' => $img, 'desc' => isset($p['description']) ? $p['description'] : '',
                'atributo' => $attr, 'opciones' => $ops,
            );
        }
        if ($n) $cats_pos[] = array('id' => $cid, 'name' => $pc['name']);
    }

    // ===== Variables para Mustache =====
    $tpl = array(
        'base' => $base, 'version' => $version, 'mis_visitas' => $mis_visitas,
        'nombre_negocio' => $nombre_negocio, 'img_logo' => $img_logo, 'celular_informes' => $celular_informes,
        'meta_titulo' => $mv('txt_titulo', $nombre_negocio),
        'meta_keywords' => $mv('txt_meta_keywords'), 'meta_description' => $mv('txt_meta_description'),
        'favicon_url' => $favicon_url,
        'tema_num' => $tema_num, 'tema_css' => $temas[$tema_num][0] . '.css', 'temas' => $temas_list,
        'links' => array(
            array('href' => '#inicio', 'label' => 'Inicio'), array('href' => '#productos', 'label' => 'Productos'),
            array('href' => '#empresa', 'label' => 'Empresa'), array('href' => '#servicios', 'label' => 'Servicios'),
            array('href' => '#clientes', 'label' => 'Clientes'), array('href' => '#contacto', 'label' => 'Contacto'),
        ),
        'sliders' => $sliders,
        'marquesina' => $g('txt_inicio', 'txt_marquesina'),
        'hero_titulo' => $ct('txt_inicio', 'texto01', $nombre_negocio),
        'hero_texto'  => $ct('txt_inicio', 'texto02'),
        'propuesta' => $propuesta,
        'categorias' => $cats_pos, 'productos' => array_values($prods), 'hay_productos' => count($prods),
        'hist' => $hist, 'hitos' => $hitos,
        'hist_img1' => gc_img($hist['img'], 'hist1'), 'hist_img2' => gc_img($hist['img2'], 'hist2'),
        'mision' => $one('txt_empresa', 'txt_mision_vision', 'txt_mision'),
        'vision' => $one('txt_empresa', 'txt_mision_vision', 'txt_vision'),
        'valores' => $g('txt_empresa', 'txt_valores'),
        'servicios_titulo' => $ct('txt_servicios', 'texto01', 'Servicios'),
        'servicios_texto'  => $ct('txt_servicios', 'texto02'),
        'servicios' => $g('txt_servicios', 'txt_servicios_lista'),
        'proceso' => $g('txt_servicios', 'txt_proceso'),
        'videos' => $videos, 'hay_videos' => count($videos),
        'clientes_titulo' => $ct('txt_clientes', 'texto01', 'Clientes'),
        'testimonios' => $g('txt_clientes', 'txt_testimonios'),
        'aliados' => $g('txt_clientes', 'txt_aliados'),
        'contacto_titulo' => $ct('txt_contacto', 'texto01', 'Contáctanos'),
        'direccion' => $one('txt_contacto', 'txt_datos_contacto', 'txt_direccion'),
        'telefono' => $tel, 'tel_digits' => $tel_dig, 'wa' => $wa,
        'horario' => $one('txt_contacto', 'txt_datos_contacto', 'txt_horario'),
        'email' => $one('txt_contacto', 'txt_datos_contacto', 'txt_email'),
        'mapa_url' => $mapa['contenido'],
        'faq' => $faq,
        'tiktok' => $mv('txt_tiktok'), 'facebook' => $mv('txt_facebook'),
        'dueno' => $mv('txt_dueno_negocio'), 'creditos' => $mv('txt_regentis'),
    );

    // JSON que lee el JS (se imprime con {{{page_data_json}}}, sin escapar)
    $js = array(
        'neg_id' => $neg_id, 'wa' => $wa, 'moneda' => 'S/',
        'msg' => $mv('txt_mensaje_whasapp', 'Hola, quisiera hacer un pedido:'),
        'productos' => $js_prods,
    );
    $json = json_encode($js, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE);
    $tpl['page_data_json'] = ($json !== false) ? $json : '{"productos":[]}';

    // Partials: todos los .html de comp_pag_web
    $partials = array();
    foreach (glob($vista . '/comp_pag_web/*.html') as $f) {
        $partials[basename($f, '.html')] = file_get_contents($f);
    }

    $archivo = $vista . '/index.html';
    if (!is_file($archivo)) {
        http_response_code(500);
        echo 'No existe la plantilla: ' . $archivo;
        return;
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo (new Mustache)->render(file_get_contents($archivo), $tpl, $partials);
});