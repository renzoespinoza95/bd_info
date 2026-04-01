<?php

function generar_url_amigable($texto){

    // pasar a minúsculas
    $texto = strtolower($texto);

    // quitar tildes
    $texto = strtr($texto, [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'ñ'=>'n'
    ]);

    // quitar caracteres especiales
    $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);

    // reemplazar espacios por guiones
    $texto = preg_replace('/[\s-]+/', '-', $texto);

    // quitar guiones al inicio y final
    $texto = trim($texto, '-');

    return $texto;
}


Flight::route('GET /pagweb/inicio', function () {
    include DEFINITION;
    autentificar_administrador();
    include VARPATH . '/public/admin/tab_pag_web/inicio.php';
});

/* =========================================================
   ITEM
========================================================= */
Flight::route('GET /xoxo/reg_item/listar', function(){
    try{
        include DEFINITION;
        autentificar_administrador();

        global $administrador_actual;

        $neg_id = $administrador_actual['neg_id'];

        $rows = DB::query("
          SELECT 
            i.*,
            s.titulo AS subcat_titulo,
            c.titulo AS cat_titulo
          FROM reg_item_pag_web i
          INNER JOIN reg_subcat_pag_web s 
            ON s.subcat_pag_web_id = i.subcat_pag_web_id
          INNER JOIN reg_cat_pag_web c 
            ON c.cat_pag_web_id = s.cat_pag_web_id
          ORDER BY i.item_pag_web_id DESC
        ");

        Flight::json(['status'=>'ok','data'=>$rows]);

    }catch(Exception $e){
        Flight::json(['status'=>'error','msg'=>$e->getMessage()],500);
    }
});

Flight::route('POST /xoxo/reg_item/crear', function(){
    try{
        include DEFINITION;
        autentificar_administrador();

        $data = Flight::request()->data->getData();

        $url_amigable = generar_url_amigable($data['titulo']); // 🔥

        DB::insert('reg_item_pag_web',[

            'titulo'              => $data['titulo'],
            'url_amigable'        => $url_amigable, // 🔥 NUEVO
            'contenido'           => $data['contenido'],
            'subtitulo_detalle'   => $data['subtitulo_detalle'] ?? '',
            'precio'              => $data['precio'],
            'clave_txt'           => $data['clave_txt'],
            'stock'               => $data['stock'],
            'subcat_pag_web_id'   => $data['subcat_id']

        ]);

        Flight::json(['status'=>'ok']);

    }catch(Exception $e){
        Flight::json(['status'=>'error','msg'=>$e->getMessage()]);
    }
});

Flight::route('POST /xoxo/reg_item/editar', function(){
    try{

        include DEFINITION;
        autentificar_administrador();

        $d = Flight::request()->data->getData();

        if(empty($d['item_pag_web_id'])){
            Flight::json(['status'=>'error','msg'=>'item_pag_web_id requerido']);
            return;
        }

        $url_amigable = generar_url_amigable($d['titulo']); // 🔥

        DB::update('reg_item_pag_web',[

            'titulo'              => $d['titulo'],
            'url_amigable'        => $url_amigable, // 🔥 NUEVO
            'contenido'           => $d['contenido'],
            'subtitulo_detalle'   => $d['subtitulo_detalle'] ?? '',
            'precio'              => $d['precio'],
            'clave_txt'           => $d['clave_txt'],
            'stock'               => $d['stock'],
            'subcat_pag_web_id'   => $d['subcat_id']

        ],"item_pag_web_id=%i",$d['item_pag_web_id']);

        Flight::json(['status'=>'ok']);

    }catch(Exception $e){
        Flight::json(['status'=>'error','msg'=>$e->getMessage()]);
    }
});

Flight::route('POST /xoxo/reg_item/eliminar', function(){
    try{
        $d = Flight::request()->data->getData();

        DB::delete('reg_item_pag_web',"item_pag_web_id=%i",$d['item_pag_web_id']);

        Flight::json(['status'=>'ok']);

    }catch(Exception $e){
        Flight::json(['status'=>'error','msg'=>$e->getMessage()]);
    }
});

/* =========================================================
   CATEGORIA
========================================================= */
Flight::route('GET /xoxo/reg_cat/listar', function(){
    include DEFINITION;

    $rows = DB::query("
        SELECT 
            cat_pag_web_id,
            titulo,
            clave_txt
        FROM reg_cat_pag_web
        ORDER BY orden
    ");

    Flight::json(['status'=>'ok','data'=>$rows]);
});

Flight::route('POST /xoxo/reg_cat/crear', function(){
    include DEFINITION;

    $d = Flight::request()->data->getData();

    DB::insert('reg_cat_pag_web',[
        'titulo'=>$d['titulo'],
        'neg_id'=>$administrador_actual['neg_id']
    ]);

    Flight::json(['status'=>'ok']);
});

Flight::route('POST /xoxo/reg_cat/editar', function(){
    $d = Flight::request()->data->getData();

    DB::update('reg_cat_pag_web',[
        'titulo'    => $d['titulo'],
        'clave_txt' => $d['clave_txt'] ?? ''
    ],"cat_pag_web_id=%i",$d['cat_pag_web_id']);

    Flight::json(['status'=>'ok']);
});

Flight::route('POST /xoxo/reg_cat/eliminar', function(){
    $d = Flight::request()->data->getData();

    DB::delete('reg_cat_pag_web',"cat_pag_web_id=%i",$d['cat_pag_web_id']);

    Flight::json(['status'=>'ok']);
});

/* =========================================================
   SUBCATEGORIA
========================================================= */
Flight::route('GET /xoxo/reg_subcat/listar', function(){

    include DEFINITION;
    autentificar_administrador();

    $rows = DB::query("
        SELECT 
            s.*,
            c.titulo AS categoria
        FROM reg_subcat_pag_web s
        LEFT JOIN reg_cat_pag_web c 
            ON c.cat_pag_web_id = s.cat_pag_web_id
        ORDER BY s.subcat_pag_web_id DESC
    ");

    Flight::json(['status'=>'ok','data'=>$rows]);
});

Flight::route('POST /xoxo/reg_subcat/crear', function(){
    $d = Flight::request()->data->getData();

    DB::insert('reg_subcat_pag_web',[
        'titulo'=>$d['titulo'],
        'cat_pag_web_id'=>$d['cat_id']
    ]);

    Flight::json(['status'=>'ok']);
});

Flight::route('POST /xoxo/reg_subcat/editar', function(){
    $d = Flight::request()->data->getData();

    DB::update('reg_subcat_pag_web',[
        'titulo'         => $d['titulo'],
        'cat_pag_web_id' => $d['cat_id'],   // 🔥 CLAVE
        'clave_txt'      => $d['clave_txt'] ?? ''
    ],"subcat_pag_web_id=%i",$d['subcat_pag_web_id']);

    Flight::json(['status'=>'ok']);
});

Flight::route('POST /xoxo/reg_subcat/eliminar', function(){
    $d = Flight::request()->data->getData();

    DB::delete('reg_subcat_pag_web',"subcat_pag_web_id=%i",$d['subcat_pag_web_id']);

    Flight::json(['status'=>'ok']);
});

/* =========================================================
   IMAGENES
========================================================= */
Flight::route('GET /xoxo/reg_img/listar', function(){
    $id = $_GET['id'];

    $rows = DB::query("
        SELECT *
        FROM reg_pag_item_img
        WHERE item_pag_web_id=%i
        ORDER BY orden ASC
    ",$id);

    Flight::json(['status'=>'ok','data'=>$rows]);
});

Flight::route('POST /xoxo/reg_img/crear', function(){

    include DEFINITION;
    autentificar_administrador();

    $item = $_POST['item'] ?? null;

    if (!$item) {
        Flight::json(['status'=>'error','msg'=>'item requerido']);
        return;
    }

    if (empty($_FILES['file']['tmp_name'])) {
        Flight::json(['status'=>'error','msg'=>'archivo requerido']);
        return;
    }

    try {

        // 🔥 1. REDIMENSIONAR
        $jpgPath = resizeTo800Jpg($_FILES['file']['tmp_name']);

        if (!file_exists($jpgPath)) {
            throw new Exception('Error al generar imagen');
        }

        // 🔥 2. NOMBRE
        $filename = 'img_' . date('Ymd_His') . '_' . rand(1000,9999) . '.jpg';

        // 🔥 3. SUBIR A BUNNY
        if (!bunnyUpload($jpgPath, $filename)) {
            throw new Exception('Error al subir a Bunny');
        }

        // 🔥 4. URL COMPLETA
        $url = rtrim(BUNNY_CDN_BASE, '/') . '/' . SLIDER_DIR . '/' . $filename;

        // 🔥 5. GUARDAR
        DB::insert('reg_pag_item_img', [
            'item_pag_web_id' => $item,
            'url_img' => $url
        ]);

        Flight::json(['status'=>'ok','url'=>$url]);

    } catch(Exception $e){
        Flight::json(['status'=>'error','msg'=>$e->getMessage()]);
    }
});

Flight::route('POST /xoxo/reg_img/eliminar', function(){

    include DEFINITION;
    autentificar_administrador();

    $d = Flight::request()->data->getData();

    $id = $d['id'] ?? null;

    if (!$id) {
        Flight::json(['status'=>'error']);
        return;
    }

    // 🔥 obtener imagen
    $row = DB::queryFirstRow(
        "SELECT url_img FROM reg_pag_item_img WHERE pag_item_img_id=%i",
        $id
    );

    if ($row && !empty($row['url_img'])) {
        bunnyDelete($row['url_img']);
    }

    DB::delete('reg_pag_item_img',"pag_item_img_id=%i",$id);

    Flight::json(['status'=>'ok']);
});

/* =========================================================
   VIDEOS
========================================================= */
Flight::route('GET /xoxo/reg_vid/listar', function(){
    $id = $_GET['id'];

    $rows = DB::query("
        SELECT *
        FROM reg_pag_item_vid
        WHERE item_pag_web_id=%i
    ",$id);

    Flight::json(['status'=>'ok','data'=>$rows]);
});

Flight::route('POST /xoxo/reg_vid/crear', function(){

    $d = Flight::request()->data->getData();

    $url = trim($d['codigo']); // 👈 aquí viene el link vimeo

    // 🔥 EXTRAER ID
    preg_match('/vimeo\.com\/(\d+)/', $url, $m);
    $video_id = $m[1] ?? null;

    if(!$video_id){
        Flight::json(['status'=>'error','msg'=>'URL Vimeo inválida']);
        return;
    }

    // 🔥 EMBED
    $embed = "https://player.vimeo.com/video/".$video_id;

    // 🔥 THUMBNAIL
    $thumb = null;

    try{
        $json = file_get_contents("https://vimeo.com/api/oembed.json?url=".$url);
        $data = json_decode($json, true);
        $thumb = $data['thumbnail_url'] ?? null;
    }catch(Exception $e){}

    DB::insert('reg_pag_item_vid',[
        'item_pag_web_id'=>$d['item'],
        'titulo'=>$d['titulo'],
        'codigo_web'=>$embed, // 👈 embed
        'url_img'=>$thumb // 👈 miniatura
    ]);

    Flight::json(['status'=>'ok']);

});

Flight::route('POST /xoxo/reg_vid/eliminar', function(){
    $d = Flight::request()->data->getData();

    DB::delete('reg_pag_item_vid',"pag_item_vid_id=%i",$d['id']);

    Flight::json(['status'=>'ok']);
});

Flight::route('POST /xoxo/reg_img/ordenar', function(){

    include DEFINITION;
    autentificar_administrador();

    $data = json_decode(Flight::request()->getBody(), true);

    if (!isset($data['orden'])) {
        Flight::json(['status'=>'error']);
        return;
    }

    DB::startTransaction();

    try{

        foreach($data['orden'] as $item){

            DB::query(
                "UPDATE reg_pag_item_img 
                 SET orden = %i 
                 WHERE pag_item_img_id = %i",
                (int)$item['orden'],
                (int)$item['id']
            );
        }

        DB::commit();

        Flight::json(['status'=>'ok']);

    }catch(Exception $e){

        DB::rollback();
        Flight::json(['status'=>'error']);

    }
});

Flight::route('POST /xoxo/reg_vid/editar', function(){

    $d = Flight::request()->data->getData();

    DB::update('reg_pag_item_vid',[
        'titulo' => $d['titulo']
    ],"pag_item_vid_id=%i",$d['id']);

    Flight::json(['status'=>'ok']);

});

Flight::route('POST /xoxo/reg_img/crear_url', function(){

    include DEFINITION;
    autentificar_administrador();

    $d = Flight::request()->data->getData();

    if(empty($d['url']) || empty($d['item'])){
        Flight::json(['status'=>'error','msg'=>'datos incompletos']);
        return;
    }

    DB::insert('reg_pag_item_img', [
        'item_pag_web_id' => $d['item'],
        'url_img' => trim($d['url'])
    ]);

    Flight::json(['status'=>'ok']);

});