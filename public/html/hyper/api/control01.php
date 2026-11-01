<?php
// ==========================================
// ENDPOINT GET /radix (Radiografía de la Plantilla con Validación y Negocio Global)
// ==========================================
Flight::route('GET /radix', function () {
    include DEFINITION;
    global $neg_id; // Obtenemos el neg_id global en memoria

    // 🔥 Validación segura usando la clase perso::vari()
    $radix_val = 0;
    try {
        if (class_exists('perso') && method_exists('perso', 'vari')) {
            $radix_val = perso::vari('radix');
        } elseif (function_exists('vari')) {
            $radix_val = vari('radix');
        }
    } catch (Exception $e) {
        $radix_val = 0;
    }

    if (intval($radix_val) !== 1) {
        header("HTTP/1.1 403 Forbidden");
        header("Content-Type: text/plain; charset=utf-8");
        echo "Acceso denegado. La radiografía se encuentra desactivada.";
        return;
    }

    // 1. Obtener todas las claves_txt de la BD
    $items_claves = DB::query("SELECT clave_txt, titulo FROM reg_item_pag_web WHERE clave_txt IS NOT NULL AND clave_txt != ''");
    $subcat_claves = DB::query("SELECT clave_txt, titulo FROM reg_subcat_pag_web WHERE clave_txt IS NOT NULL AND clave_txt != ''");
    $cat_claves = DB::query("SELECT clave_txt, titulo FROM reg_cat_pag_web WHERE clave_txt IS NOT NULL AND clave_txt != ''");

    // 2. Obtener la jerarquía de Categorías, Subcategorías y sus Relaciones filtradas por el neg_id global
    $categorias = DB::query("
        SELECT c.cat_pag_web_id, c.titulo as cat_titulo, c.clave_txt as cat_clave,
               s.subcat_pag_web_id, s.titulo as subcat_titulo, s.clave_txt as subcat_clave
        FROM reg_cat_pag_web c
        LEFT JOIN reg_subcat_pag_web s ON s.cat_pag_web_id = c.cat_pag_web_id
        WHERE c.neg_id = %i OR c.neg_id IS NULL
        ORDER BY c.orden ASC, s.orden ASC
    ", $neg_id);

    // 3. Obtener variables del sistema (reg_vari)
    $variables = DB::query("SELECT nombre, valor FROM reg_vari");

    // 4. Obtener datos del negocio actual utilizando la variable global $neg_id
    $negocio = DB::queryFirstRow("SELECT nombre FROM reg_neg WHERE neg_id = %i LIMIT 1", $neg_id);
    $nombre_negocio = $negocio['nombre'] ?? 'HYPRLUV';
    
    $visitas = 0;
    try {
        $visitas = class_exists('perso') ? perso::vari("visitas") : 0;
    } catch (Exception $e) {
        $visitas = 0;
    }

    // Construcción del texto ASCII
    $ascii = "====================================================\n";
    $ascii .= "         RADIOGRAFIA DE PLANTILLA (GET /radix)      \n";
    $ascii .= "====================================================\n\n";

    $ascii .= "[+] INFORMACION GENERAL & RENDER\n";
    $ascii .= "    - Negocio ID (Global) : " . ($neg_id ?? 'NO DEFINIDO') . "\n";
    $ascii .= "    - Nombre del Negocio  : " . $nombre_negocio . "\n";
    $ascii .= "    - Visitas Totales     : " . $visitas . "\n\n";

    $ascii .= "[+] VARIABLES DEL SISTEMA (reg_vari)\n";
    foreach ($variables as $v) {
        $ascii .= "    - " . $v['nombre'] . " = " . ($v['valor'] ?? 'NULL') . "\n";
    }
    $ascii .= "\n";

    $ascii .= "[+] CLAVES_TXT REGISTRADAS\n";
    $ascii .= "    --- Categorias (reg_cat_pag_web) ---\n";
    foreach ($cat_claves as $cc) {
        $ascii .= "        * " . ($cc['clave_txt'] ?: 'SIN CLAVE') . " [" . $cc['titulo'] . "]\n";
    }
    $ascii .= "    --- Subcategorias (reg_subcat_pag_web) ---\n";
    foreach ($subcat_claves as $sc) {
        $ascii .= "        * " . ($sc['clave_txt'] ?: 'SIN CLAVE') . " [" . $sc['titulo'] . "]\n";
    }
    $ascii .= "    --- Items (reg_item_pag_web) ---\n";
    foreach ($items_claves as $ic) {
        $ascii .= "        * " . ($ic['clave_txt'] ?: 'SIN CLAVE') . " [" . $ic['titulo'] . "]\n";
    }
    $ascii .= "\n";

    $ascii .= "[+] JERARQUIA Y RELACIONES (Cat -> Subcat)\n";
    $cat_map = [];
    foreach ($categorias as $row) {
        $cat_id = $row['cat_pag_web_id'];
        if (!isset($cat_map[$cat_id])) {
            $cat_map[$cat_id] = [
                'titulo' => $row['cat_titulo'],
                'clave' => $row['cat_clave'],
                'subcats' => []
            ];
        }
        if ($row['subcat_pag_web_id']) {
            $cat_map[$cat_id]['subcats'][] = [
                'titulo' => $row['subcat_titulo'],
                'clave' => $row['subcat_clave']
            ];
        }
    }

    foreach ($cat_map as $c_id => $c_data) {
        $ascii  .= "    [CAT] " . $c_data['titulo'] . " (Clave: " . ($c_data['clave'] ?: 'N/A') . ")\n";
        foreach ($c_data['subcats'] as $sub) {
            $ascii .= "       └── [SUBCAT] " . $sub['titulo'] . " (Clave: " . ($sub['clave'] ?: 'N/A') . ")\n";
        }
    }
    $ascii .= "\n====================================================\n";

    header("Content-Type: text/plain; charset=utf-8");
    echo $ascii;
});