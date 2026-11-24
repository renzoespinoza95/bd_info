<?php
class perso {

    public static function _es_apache(): bool {
        $sv = strtolower($_SERVER['SERVER_SOFTWARE'] ?? '');
        if ($sv) {
            // Coincide con Apache/2.x, Apache, httpd, etc.
            return (strpos($sv, 'apache') !== false) || (strpos($sv, 'httpd') !== false);
        }
        // Fallbacks por si SERVER_SOFTWARE no está
        return function_exists('apache_get_version') || getenv('APACHE_RUN_USER');
    }    

    public static function regentis_rss($neg_id_param) {
        try {
            $neg_id = (int)$neg_id_param;

            if ($neg_id <= 0) {
                return ['status' => 'error', 'msg' => 'neg_id inválido'];
            }

            // Consultar información básica del negocio
            $negocio = DB::queryFirstRow("SELECT neg_id, nombre, slug, img_logo, celular_informes FROM reg_neg WHERE neg_id = %i", $neg_id);

            // ---------------- WEB ----------------
            $categoriasWeb = DB::query("SELECT * FROM reg_cat_pag_web WHERE neg_id = %i AND (is_visible = 1 OR is_visible IS NULL) ORDER BY cat_pag_web_id DESC", $neg_id);
            $arbolWeb = [];

            foreach ($categoriasWeb as $cat) {
                $cat = self::arbol_cast($cat, ['cat_pag_web_id', 'is_visible', 'orden', 'neg_id']);

                $subcats = DB::query("SELECT * FROM reg_subcat_pag_web WHERE cat_pag_web_id = %i AND (is_visible = 1 OR is_visible IS NULL) ORDER BY orden ASC, subcat_pag_web_id ASC", $cat['cat_pag_web_id']);
                $subcatsList = [];

                foreach ($subcats as $sub) {
                    $sub = self::arbol_cast($sub, ['subcat_pag_web_id', 'cat_pag_web_id', 'is_visible', 'orden']);

                    $items = DB::query("SELECT * FROM reg_item_pag_web WHERE subcat_pag_web_id = %i AND (is_visible = 1 OR is_visible IS NULL) ORDER BY orden ASC, item_pag_web_id ASC", $sub['subcat_pag_web_id']);
                    $itemsList = [];

                    foreach ($items as $item) {
                        $item = self::arbol_cast($item, ['item_pag_web_id', 'subcat_pag_web_id', 'stock', 'orden', 'is_visible'], ['precio']);

                        $imagenes = DB::query("SELECT * FROM reg_pag_item_img WHERE item_pag_web_id = %i ORDER BY orden ASC, pag_item_img_id ASC", $item['item_pag_web_id']);
                        foreach ($imagenes as $k => $im) {
                            $imagenes[$k] = self::arbol_cast($im, ['pag_item_img_id', 'item_pag_web_id', 'is_visible', 'orden']);
                        }

                        $videos = DB::query("SELECT * FROM reg_pag_item_vid WHERE item_pag_web_id = %i ORDER BY orden ASC, pag_item_vid_id ASC", $item['item_pag_web_id']);
                        foreach ($videos as $k => $vi) {
                            $videos[$k] = self::arbol_cast($vi, ['pag_item_vid_id', 'item_pag_web_id', 'is_visible', 'orden']);
                        }

                        $item['imagenes'] = $imagenes;
                        $item['videos'] = $videos;
                        $itemsList[] = $item;
                    }

                    $sub['items'] = $itemsList;
                    $subcatsList[] = $sub;
                }

                $cat['subcategorias'] = $subcatsList;
                $arbolWeb[] = $cat;
            }

            // ---------------- POS ----------------
            $categoriasPos = DB::query("SELECT * FROM pos_category WHERE neg_id = %i AND borrado_el IS NULL ORDER BY category_id DESC", $neg_id);
            $arbolPos = [];

            foreach ($categoriasPos as $pcat) {
                $pcat = self::arbol_cast($pcat, ['category_id', 'priority', 'neg_id', 'categoria_global_id', 'is_activo']);

                // Subcategorías POS de la categoría
                $subsPos = DB::query("SELECT * FROM pos_subcategoria WHERE category_id = %i AND borrado_el IS NULL ORDER BY orden ASC, subcategoria_id ASC", $pcat['category_id']);
                $subsPosList = [];
                $mapaClaveSub = []; // subcategoria_id => clave_txt
                foreach ($subsPos as $sp) {
                    $sp = self::arbol_cast($sp, ['subcategoria_id', 'category_id', 'is_activo', 'orden']);
                    $mapaClaveSub[$sp['subcategoria_id']] = $sp['clave_txt'] ?? null;
                    $subsPosList[] = $sp;
                }

                // Productos de la categoría (a través de la tabla puente)
                $relaciones = DB::query("SELECT * FROM pos_product_category WHERE category_id = %i AND is_visible = 1", $pcat['category_id']);
                $productosList = [];

                foreach ($relaciones as $rel) {
                    $prod = DB::queryFirstRow("SELECT * FROM pos_product WHERE product_id = %i AND neg_id = %i AND borrado_el IS NULL", $rel['product_id'], $neg_id);
                    if (!$prod) {
                        continue;
                    }

                    $prod = self::arbol_cast($prod, ['product_id', 'neg_id', 'subcategoria_id', 'is_visible', 'cantidad_comprada'], ['price', 'compra_total']);

                    // Clave de la subcategoría del producto (sobrevive a la limpieza de *_id del JSON)
                    $prod['subcategoria_clave_txt'] = ($prod['subcategoria_id'] !== null && isset($mapaClaveSub[$prod['subcategoria_id']]))
                        ? $mapaClaveSub[$prod['subcategoria_id']]
                        : null;

                    if (isset($prod['variantes_json']) && $prod['variantes_json'] !== '') {
                        $dec = json_decode($prod['variantes_json'], true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $prod['variantes_json'] = $dec;
                        }
                    }

                    $pimagenes = DB::query("SELECT * FROM pos_product_image WHERE product_id = %i AND borrado_el IS NULL ORDER BY orden ASC, product_image_id ASC", $prod['product_id']);
                    foreach ($pimagenes as $k => $pim) {
                        $pimagenes[$k] = self::arbol_cast($pim, ['product_image_id', 'product_id', 'orden', 'is_visible']);
                    }

                    $prod['imagenes'] = $pimagenes;
                    $productosList[] = $prod;
                }

                $pcat['subcategorias'] = $subsPosList;
                $pcat['productos'] = $productosList;
                $arbolPos[] = $pcat;
            }

            // ---------------- SLIDERS ----------------
            $sliders = DB::query("SELECT * FROM reg_slider WHERE neg_id = %i ORDER BY slider_id DESC", $neg_id);
            foreach ($sliders as $k => $sl) {
                $sliders[$k] = self::arbol_cast($sl, ['slider_id', 'orden', 'is_visible', 'neg_id']);
            }

            return [
                'status' => 'ok',
                'negocio' => $negocio,
                'web' => $arbolWeb,
                'pos' => $arbolPos,
                'sliders' => $sliders
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }   

    public static function vari($nombre_variable) {
        $query = <<<EOF
        SELECT * FROM reg_vari WHERE nombre = '$nombre_variable'
        EOF;

        $res = DB::queryFirstRow($query);
        $vari = $res['valor'];    

        if($vari == "") {
            echo "NO EXISTE: $nombre_variable";
            exit;
        }
        return $vari;
    }

    /**
     * Obtiene el valor actual de las visitas para el negocio actual.
     */
    public static function obtener_visitas() {
        global $neg_id; // Aseguramos el acceso a la variable global en memoria

        $item_visitas = DB::queryFirstRow("
            SELECT i.contenido 
            FROM reg_item_pag_web i
            INNER JOIN reg_subcat_pag_web s ON i.subcat_pag_web_id = s.subcat_pag_web_id
            INNER JOIN reg_cat_pag_web c ON s.cat_pag_web_id = c.cat_pag_web_id
            WHERE i.clave_txt = %s AND c.neg_id = %i
            LIMIT 1
        ", 'txt_visitas', $neg_id);

        return isset($item_visitas['contenido']) ? (int) $item_visitas['contenido'] : 0;
    }

    /**
     * Incrementa en 1 el contador de visitas filtrando por el negocio actual.
     * Si no existe el registro de visitas para este negocio, lo crea.
     */
    public static function visita() {
        global $neg_id; // Aseguramos el acceso a la variable global en memoria

        // 1. Buscamos el item de visitas vinculado al negocio a través de sus categorías
        $item = DB::queryFirstRow("
            SELECT i.item_pag_web_id, i.contenido 
            FROM reg_item_pag_web i
            INNER JOIN reg_subcat_pag_web s ON i.subcat_pag_web_id = s.subcat_pag_web_id
            INNER JOIN reg_cat_pag_web c ON s.cat_pag_web_id = c.cat_pag_web_id
            WHERE i.clave_txt = %s AND c.neg_id = %i
            LIMIT 1
        ", 'txt_visitas', $neg_id);

        if ($item) {
            // Si ya existe, incrementamos el valor actual
            $nuevo_total = (int) $item['contenido'] + 1;
            
            DB::update('reg_item_pag_web', [
                'contenido' => (string) $nuevo_total
            ], 'item_pag_web_id = %i', $item['item_pag_web_id']);
            
            return $nuevo_total;
        } else {
            // 2. Si no existe, necesitamos asociarlo a una subcategoría válida de ese negocio.
            // Buscamos la primera subcategoría perteneciente a una categoría de este $neg_id.
            $subcat = DB::queryFirstRow("
                SELECT s.subcat_pag_web_id 
                FROM reg_subcat_pag_web s
                INNER JOIN reg_cat_pag_web c ON s.cat_pag_web_id = c.cat_pag_web_id
                WHERE c.neg_id = %i
                LIMIT 1
            ", $neg_id);

            if ($subcat) {
                // Creamos el registro de visitas vinculado a la subcategoría del negocio
                DB::insert('reg_item_pag_web', [
                    'subcat_pag_web_id' => $subcat['subcat_pag_web_id'],
                    'clave_txt' => 'txt_visitas',
                    'titulo' => 'Contador de Visitas',
                    'contenido' => '1'
                ]);
                
                return 1;
            }
            
            // Retorna 0 si el negocio aún no tiene categorías ni subcategorías creadas
            return 0; 
        }
    }

    // =========================================================================
    // MÉTODOS AUXILIARES (ARBOL Y CAMPOS)
    // =========================================================================

    // Convierte a int / float las columnas indicadas (MySQL suele devolver todo como string)
    public static function arbol_cast($row, $ints = array(), $floats = array()) {
        foreach ($ints as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $row[$c] = (int)$row[$c];
            }
        }
        foreach ($floats as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $row[$c] = (float)$row[$c];
            }
        }
        return $row;
    }

    // Devuelve solo las columnas pedidas que existan en el JSON y no sean null
    public static function imp_campos($j, $cols) {
        $out = array();
        foreach ($cols as $c) {
            if (array_key_exists($c, $j) && $j[$c] !== null) {
                $out[$c] = $j[$c];
            }
        }
        return $out;
    }

    public static function imp_vacio($v) {
        return $v === null || $v === '';
    }

    // +++++++++++++
    // | END PERSO |
    // +++++++++++++

}