<!-- =========================================
     PAG WEB ADMIN (COMPLETO)
========================================= -->

<div class="row-fluid" id="appPagWeb">
  <div class="span12">

    <!-- =========================
         HEADER
    ========================== -->
    <div class="titulo-fijo clearfix">

      <div style="float:left;">
        <h2 style="margin:0;">Items Web</h2>
      </div>

      <div class="btn-group pull-right">
        <button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
          <i class="fa fa-cog"></i>
          <span class="caret"></span>
        </button>

        <ul class="dropdown-menu pull-right">
          <li><a href="#" @click.prevent="abrirCategorias">Ver categorías</a></li>
          <li><a href="#" @click.prevent="abrirSubcategorias">Ver subcategorías</a></li>
          <li class="divider"></li>
          <li><a href="#" @click.prevent="abrirNuevoItem">Nuevo item</a></li>
        </ul>
      </div>

    </div>

    <!-- =========================
         TABLA PRINCIPAL
    ========================== -->
    <div class="span12 tabla_esp_sup">
    <table id="tablaItems" class="table table-bordered table-condensed sel-fila">
      <thead>
        <tr>
          <th>ID</th>
          <th>Titulo</th>
          <th>CLAVE_TXT</th>
          <th>Categoría</th>
          <th>Subcategoría</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    </div>
  </div>


<!-- =========================================================
   MODAL CATEGORIAS
========================================================= -->
<div id="modalCategorias" class="modal hide fade fullscreen">
  <div class="modal-header">
    <h3>Categorías</h3>
  </div>

  <div class="modal-body">
    <table id="tablaCat" class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th>ID</th>
          <th>Titulo</th>
          <th>Clave</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="modal-footer">
    <button class="btn btn-success" @click="abrirCrearCategoria">Agregar</button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>

<!-- =========================================================
   MODAL CREAR CATEGORIA
========================================================= -->
<div id="modalCrearCategoria" class="modal hide fade fullscreen">
  <div class="modal-header"><h3>Nueva Categoría</h3></div>
  <div class="modal-body">
    <input v-model="catForm.titulo" class="input-xxlarge" placeholder="Titulo">
  </div>
  <div class="modal-footer">
    <button class="btn btn-primary" @click="guardarCategoria">Guardar</button>
    <button class="btn" data-dismiss="modal">Cancelar</button>
  </div>
</div>

<!-- =========================================================
   MODAL SUBCATEGORIAS
========================================================= -->
<div id="modalSubcategorias" class="modal hide fade fullscreen">
  <div class="modal-header">
    <h3>Subcategorías</h3>
  </div>

  <div class="modal-body">
    <table id="tablaSubcat" class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th>ID</th>
          <th>Titulo</th>
          <th>Clave</th>
          <th>Categoría</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

  </div>

  <div class="modal-footer">
    <button class="btn btn-success" @click="abrirCrearSubcat">
      <i class="icon-plus icon-white"></i> Agregar
    </button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>

<div id="modalCrearSubcat" class="modal hide fade fullscreen">
  <div class="modal-header">
    <h3>Nueva Subcategoría</h3>
  </div>

  <div class="modal-body">

    <input v-model="subcatForm.titulo" class="input-xxlarge" placeholder="Titulo"><br><br>

    <v-select
      :options="categorias"
      label="titulo"
      v-model="subcatForm.cat"
      placeholder="Selecciona categoría"
    ></v-select>

  </div>

  <div class="modal-footer">
    <button class="btn btn-primary" @click="guardarSubcat">Guardar</button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>

<!-- =========================================================
   MODAL ITEM
========================================================= -->
<div id="modalItem" class="modal hide fade fullscreen">
  <div class="modal-header">
    <h3>Nuevo Item</h3>
  </div>

  <div class="modal-body">

    <div class="row-fluid">

      <div class="span6">
        <input v-model="itemForm.titulo" 
               class="input-xxlarge" 
               style="width:100%;" 
               placeholder="Titulo">
      </div>

      <div class="span6">
        <input v-model="itemForm.clave_txt" 
               class="input-xxlarge" 
               style="width:100%;" 
               placeholder="Clave TXT">
      </div>

    </div>

    <br>

    <div class="row-fluid">

      <div class="span4">
        <v-select 
          :options="categorias" 
          label="titulo" 
          v-model="itemForm.cat"
          placeholder="Categoría">
        </v-select>
      </div>

      <div class="span4">
        <v-select 
          :options="subcatsFiltradas" 
          label="titulo" 
          v-model="itemForm.subcat"
          placeholder="Subcategoría">
        </v-select>
      </div>

      <div class="span4">
        <input v-model="itemForm.precio" 
               class="input-small" 
               style="width:100%;" 
               placeholder="Precio">
      </div>

    </div>

    <br>

    <!-- 🔥 CONTENIDO -->
    <div class="row-fluid">
      <div class="span12">
        <label><b>Contenido</b></label>
        <textarea id="txtContenido"></textarea>
      </div>
    </div>

    <br>

    <!-- 🔥 NUEVO: SUBTITULO DETALLE -->
    <div class="row-fluid">
      <div class="span12">
        <label><b>Subtitulo Detalle (40 palabras)</b></label>
        <textarea id="txtSubtitulo"></textarea>
      </div>
    </div>

  </div>

  <div class="modal-footer">
    <button class="btn btn-primary" @click="guardarItem">Guardar</button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>

<!-- =========================================================
   MODAL IMAGENES
========================================================= -->
<div id="modalImagenes" class="modal hide fade fullscreen">
  <div class="modal-header"><h3>Lista de Imágenes</h3></div>

  <div class="modal-body">

    <div id="contenedorImagenes" style="display:flex;flex-wrap:wrap;gap:15px;">

      <div v-for="(img,index) in imagenes"
           :key="img.pag_item_img_id"
           class="img-item"
           :data-id="img.pag_item_img_id">

        <!-- 🔥 CÍRCULO ORDEN -->
        <div class="orden-circle">
          {{ index + 1 }}
        </div>

        <img :src="img.url_img">

        <br>

        <button class="btn btn-warning btn-mini"
                @click="eliminarImagen(img)">
          <i class="fa fa-trash"></i>
        </button>

        <button class="btn btn-default btn-mini"
                @click="copiarImagen(img)">
          <i class="fa fa-copy"></i>
        </button>

      </div>

    </div>

  </div>

  <div class="modal-footer">
    <button class="btn btn-info" @click="abrirPegarImagen">
      <i class="fa fa-paste"></i> Pegar imagen
    </button>
    <button class="btn btn-success" @click="abrirSubirImagen">Subir imagen</button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>


<div id="modalEditarSubcat" class="modal hide fade fullscreen">
  <div class="modal-header">
    <h3>Editar Subcategoría</h3>
  </div>

  <div class="modal-body">

    <input v-model="subcatForm.titulo" class="input-xxlarge" placeholder="Titulo"><br><br>

    <input v-model="subcatForm.clave_txt" class="input-xxlarge" placeholder="Clave TXT"><br><br>

    <v-select
      :options="categorias"
      label="titulo"
      v-model="subcatForm.cat"
    ></v-select>

  </div>

  <div class="modal-footer">
    <button class="btn btn-primary" @click="actualizarSubcat">Guardar</button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>

<!-- =========================================================
   MODAL SUBIR IMAGEN
========================================================= -->
<div id="modalSubirImagen" class="modal hide fade">
  <div class="modal-header"><h3>Subir Imagen</h3></div>

  <div class="modal-body">
    <input type="file" id="fileImg" @change="previewImg"><br><br>

    <div v-if="preview" style="width:150px;height:150px;">
      <img :src="preview" style="width:100%;">
    </div>
  </div>

  <div class="modal-footer">
    <button class="btn btn-primary" @click="guardarImagen">Guardar</button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>


<div id="modalPegarImagen" class="modal hide fade fullscreen">
  <div class="modal-header">
    <h3>Pegar URL de Imagen</h3>
  </div>

  <div class="modal-body">

    <input v-model="urlImagenPegar"
           class="input-xxlarge"
           placeholder="https://...">

    <br><br>

    <div v-if="urlImagenPegar" style="width:150px;">
      <img :src="urlImagenPegar" style="width:100%;">
    </div>

  </div>

  <div class="modal-footer">
    <button class="btn btn-primary" @click="guardarImagenDesdeURL">
      Guardar
    </button>
    <button class="btn" data-dismiss="modal">Cancelar</button>
  </div>
</div>

<!-- =========================================================
   MODAL VIDEOS
========================================================= -->
<div id="modalVideos" class="modal hide fade fullscreen">
  <div class="modal-header"><h3>Lista de videos</h3></div>

  <div class="modal-body">
    <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <div v-for="v in videos" 
             style="width:150px; cursor:pointer;"
             @click="verVideo(v)">

          <img :src="v.url_img" style="width:100%; border-radius:6px;">

          <div style="font-size:12px; margin-top:5px;">
            {{ v.titulo }}
          </div>

          <button class="btn btn-warning btn-mini" 
                  @click.stop="eliminarVideo(v)">
            <i class="fa fa-trash"></i>
          </button>
          <button class="btn btn-info btn-mini" 
                  @click.stop="editarVideo(v)">
            <i class="fa fa-pencil"></i>
          </button>

          <button class="btn btn-default btn-mini" 
                  @click.stop="copiarCodigo(v)">
            <i class="fa fa-copy"></i>
          </button>

        </div>
    </div>
  </div>

  <div class="modal-footer">
    <button class="btn btn-success" @click="abrirSubirVideo">Subir video</button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>


<div id="modalEditarCategoria" class="modal hide fade fullscreen">
  <div class="modal-header">
    <h3>Editar Categoría</h3>
  </div>

  <div class="modal-body">
    <input v-model="catForm.cat_pag_web_id" type="hidden">

    <input v-model="catForm.titulo" 
           class="input-xxlarge" 
           placeholder="Titulo"><br><br>

    <input v-model="catForm.clave_txt" 
           class="input-xxlarge" 
           placeholder="Clave TXT">
  </div>

  <div class="modal-footer">
    <button class="btn btn-primary" @click="actualizarCategoria">Guardar</button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>


<div id="modalVideoPlayer" class="modal hide fade fullscreen">
  <div class="modal-header">
    <h3>{{ videoActual.titulo }}</h3>
  </div>

  <div class="modal-body" style="text-align:center;">

    <iframe 
      v-if="videoActual.codigo_web"
      :src="videoActual.codigo_web"
      width="100%" 
      height="400"
      frameborder="0"
      allowfullscreen>
    </iframe>

  </div>

  <div class="modal-footer">
    <button class="btn" @click="cerrarVideo">Cerrar</button>
  </div>
</div>

<!-- =========================================================
   MODAL SUBIR VIDEO
========================================================= -->
<div id="modalSubirVideo" class="modal hide fade fullscreen">
  <div class="modal-header"><h3>Agregar video</h3></div>

  <div class="modal-body">

    <input v-model="videoForm.titulo" class="input-xxlarge" placeholder="Titulo"><br><br>

    <textarea v-model="videoForm.codigo" class="input-xxlarge" placeholder="Codigo embed"></textarea><br>

    <div id="previewVideo" style="margin-top:10px;"></div>

  </div>

  <div class="modal-footer">
    <button class="btn btn-primary" @click="guardarVideo">Guardar</button>
    <button class="btn" data-dismiss="modal">Cerrar</button>
  </div>
</div>


<div id="modalEditarVideo" class="modal hide fade fullscreen">
  <div class="modal-header">
    <h3>Editar Video</h3>
  </div>

  <div class="modal-body">
    <input v-model="videoFormEditar.titulo" 
           class="input-xxlarge" 
           placeholder="Titulo">
  </div>

  <div class="modal-footer">
    <button class="btn btn-primary" @click="guardarEditarVideo">
      Guardar
    </button>
    <button class="btn" data-dismiss="modal">Cancelar</button>
  </div>
</div>



</div>
<style>
.img-item {
  position: relative;
  width: 120px;
  text-align: center;
  cursor: move;
}

.img-item img {
  width: 100px;
  height: 100px;
  object-fit: cover;
}

.orden-circle {
  position: absolute;
  top: 5px;
  right: 10px;
  background: #ff5722;
  color: #fff;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  font-size: 12px;
  line-height: 22px;
  text-align: center;
  font-weight: bold;
}  
</style>

<script>
Vue.component('v-select', VueSelect.VueSelect)

 function bloquearUI(mensaje = 'Procesando...') {
  $.blockUI({
    message: '<h4 style="color:#fff;">' + mensaje + '</h4>',
    css: {
      border: 'none',
      padding: '15px',
      backgroundColor: '#000',
      borderRadius: '10px',
      opacity: .7,
      color: '#fff',
      zIndex: 2000 // 🔥 IMPORTANTE
    },
    overlayCSS: {
      backgroundColor: '#000',
      opacity: 0.6,
      zIndex: 1999 // 🔥 por debajo del mensaje pero encima de modal
    }
  });
}

  function desbloquearUI() {
    $.unblockUI();
  }

new Vue({
  el:'#appPagWeb',

  data:{
    apphost:(typeof apphost!=='undefined'?apphost:''),

    videoFormEditar:{
      pag_item_vid_id:null,
      titulo:''
    },

    items:[],
    urlImagenPegar:'',
    categorias:[],
    subcategorias:[],
    videoActual:{},
    itemForm:{
      titulo:'',
      precio:0,
      subtitulo_detalle:'', // 🔥 NUEVO
      stock:0,
      clave_txt:'', // 🔥 agregar esto
      contenido:'',
      subcat:null
    },
    catForm:{},
    videoForm:{},
    dtCat:null,
    subcatForm:{
      titulo:'',
      cat:null
    },

    imagenes:[],
    videos:[],
    dtSubcat:null,

    preview:null,
    itemActual:0
  },

  computed:{
    subcatsFiltradas(){
      if(!this.itemForm.cat) return []
      return this.subcategorias.filter(s=>s.cat_pag_web_id==this.itemForm.cat.cat_pag_web_id)
    }
  },

  methods:{

    bloquear(msg){ $.blockUI({message:`<h4>${msg}</h4>`}) },

    listar(){

      this.bloquear('Cargando...')

      axios.get(`${this.apphost}/xoxo/reg_item/listar`)
      .then(r=>{

        this.items = r.data.data || []

        this.$nextTick(()=>{

          if(!this.dt){

            this.dt = $('#tablaItems').DataTable({
              language: (typeof dt_language !== 'undefined' ? dt_language : undefined),
              scrollX: true,
              dom: 'frtip',
              order: [[0,'desc']]
            });

            const self = this

            $('#tablaItems tbody')
              .on('click','.editar',function(){
                const id = $(this).data('id')
                const row = self.items.find(x=>x.item_pag_web_id==id)
                self.abrirEditarItem(row)
              })
              .on('click','.eliminar',function(){
                const id = $(this).data('id')
                self.eliminar(id)
              })
              .on('click','.img',function(){
                self.itemActual = $(this).data('id')
                self.verImagenes(self.itemActual)
              })
              .on('click','.vid',function(){
                self.itemActual = $(this).data('id')
                self.verVideos(self.itemActual)
              })

          }

          this.dt.clear()

          this.items.forEach(i=>{

            this.dt.row.add([
              i.item_pag_web_id,
              i.titulo,
              i.clave_txt,
              i.cat_titulo,
              i.subcat_titulo,
              i.precio,
              i.stock,
              `
              <div class="btn-group">
                    <button class="btn btn-mini dropdown-toggle" data-toggle="dropdown">
                      ⚙ <span class="caret"></span>
                    </button>
                  <ul class="dropdown-menu">
                  <li><a href="#" class="editar" data-id="${i.item_pag_web_id}">Editar</a></li>
                  <li><a href="#" class="eliminar" data-id="${i.item_pag_web_id}">Eliminar</a></li>
                  <li class="divider"></li>
                  <li><a href="#" class="img" data-id="${i.item_pag_web_id}">Imagen</a></li>
                  <li><a href="#" class="vid" data-id="${i.item_pag_web_id}">Video</a></li>
                </ul>
              </div>
              `
            ])

          })

          this.dt.draw()

          // 🔥🔥🔥 ESTO ES LO QUE QUIERES
          setTimeout(() => {
            agregarScrollBotones($('#tablaItems'));
          }, 200);

        })

      })
      .finally(()=>$.unblockUI())
    },

    editarVideo(v){

      this.videoFormEditar = {
        pag_item_vid_id: v.pag_item_vid_id,
        titulo: v.titulo
      };

      $('#modalVideos').modal('hide');

      setTimeout(()=>{
        $('#modalEditarVideo').modal('show');
      },200);

    },   

    abrirPegarImagen(){

      this.urlImagenPegar = '';

      $('#modalImagenes').modal('hide');

      setTimeout(()=>{
        $('#modalPegarImagen').modal('show');
      },200);

    },     

    guardarImagenDesdeURL(){

      if(!this.urlImagenPegar){
        return apprise('Pega una URL válida');
      }

      this.bloquear('Guardando...');

      axios.post(`${this.apphost}/xoxo/reg_img/crear_url`,{
        url: this.urlImagenPegar,
        item: this.itemActual
      })
      .then(()=>{

        $('#modalPegarImagen').modal('hide');

        setTimeout(()=>{
          this.verImagenes(this.itemActual);
        },200);

      })
      .finally(()=>$.unblockUI());

    },    

    copiarImagen(img){

      const url = img.url_img;

      navigator.clipboard.writeText(url)
      .then(()=>{
        apprise('Imagen copiada 💙');
      })
      .catch(()=>{
        apprise('No se pudo copiar');
      });

    },    

    guardarEditarVideo(){

      if(!this.videoFormEditar.titulo){
        return apprise('Escribe el título');
      }

      this.bloquear('Guardando...');

      axios.post(`${this.apphost}/xoxo/reg_vid/editar`,{
        id: this.videoFormEditar.pag_item_vid_id,
        titulo: this.videoFormEditar.titulo
      })
      .then(()=>{

        $('#modalEditarVideo').modal('hide');

        setTimeout(()=>{
          this.verVideos(this.itemActual);
        },200);

      })
      .finally(()=>$.unblockUI());

    },    

    copiarCodigo(v){

      let url = v.codigo_web || '';

      // 🔥 EXTRAER ID DE VIMEO
      const match = url.match(/video\/(\d+)/);

      if(match && match[1]){

        const limpio = `https://vimeo.com/${match[1]}`;

        navigator.clipboard.writeText(limpio)
        .then(()=>{
          apprise('Link listo para guardar 😏');
        })
        .catch(()=>{
          apprise('No se pudo copiar');
        });

      }else{

        apprise('Formato de video inválido');

      }

    },   

    verVideo(v){

      this.videoActual = v;

      $('#modalVideos').modal('hide');

      setTimeout(()=>{
        $('#modalVideoPlayer').modal('show');
      },200);

    },

    cerrarVideo(){

      $('#modalVideoPlayer').modal('hide');

      setTimeout(()=>{
        $('#modalVideos').modal('show');
      },200);

    },    

    activarDragImagenes(){

      const vm = this;

      $('#contenedorImagenes').sortable({

        items: '.img-item',

        update: function(){

          let orden = [];

          $('#contenedorImagenes .img-item').each(function(index){

            orden.push({
              id: $(this).data('id'),
              orden: index + 1
            });

          });

          // 🔥 enviar orden
          fetch(vm.apphost + '/xoxo/reg_img/ordenar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orden })
          })
          .then(r=>r.json())
          .then(()=>{
            vm.verImagenes(vm.itemActual); // refrescar
          });

        }

      });

    },    

    abrirCrearSubcat(){

      this.bloquear('Cargando...')

      axios.get(`${this.apphost}/xoxo/reg_cat/listar`)
      .then(r=>{

        this.categorias = r.data.data || []

        // limpiar formulario
        this.subcatForm = {
          titulo:'',
          cat:null
        }

        $('#modalSubcategorias').modal('hide')

        setTimeout(()=>{
          $('#modalCrearSubcat').modal('show')
        },200)

      })
      .finally(()=>$.unblockUI())

    },

    guardarSubcat(){

      if(!this.subcatForm.titulo) return apprise('Escribe el título')
        if(!this.subcatForm.cat) return apprise('Selecciona categoría')

        this.bloquear('Guardando...')

      axios.post(`${this.apphost}/xoxo/reg_subcat/crear`,{
        titulo:this.subcatForm.titulo,
        cat_id:this.subcatForm.cat.cat_pag_web_id
      })
      .then(()=>{

        $('#modalCrearSubcat').modal('hide')

      // 🔥 volver al anterior
        setTimeout(()=>{
          this.abrirSubcategorias()
        },200)

      })
      .finally(()=>$.unblockUI())

    },  

    abrirNuevoItem(){

      this.bloquear('Cargando...')

      Promise.all([
        axios.get(`${this.apphost}/xoxo/reg_cat/listar`),
        axios.get(`${this.apphost}/xoxo/reg_subcat/listar`)
      ])
      .then(([catRes, subcatRes])=>{

        this.categorias = catRes.data.data || []
        this.subcategorias = subcatRes.data.data || []

        this.itemForm = {
          titulo:'',
          precio:0,
          stock:0,
          contenido:'',
          subtitulo_detalle:'', // 🔥
          cat:null,
          subcat:null
        }

        $('#modalItem').modal('show')

        this.$nextTick(()=>{

        // 🔥 DESTRUIR SI EXISTE
        if ($('#txtContenido').next('.note-editor').length) {
          $('#txtContenido').summernote('destroy');
        }

        if ($('#txtSubtitulo').next('.note-editor').length) {
          $('#txtSubtitulo').summernote('destroy');
        }

        // 🔥 RECREAR LIMPIO
        $('#txtContenido').summernote({
          height:200
        }).summernote('code','');

        $('#txtSubtitulo').summernote({
          height:150
        }).summernote('code','');

      });

      })
      .finally(()=>$.unblockUI())
    },

    eliminarImagen(img){

      const self = this;

      apprise('¿Eliminar imagen?', { confirm:true }, function(ok){

        if(!ok) return;

        self.bloquear('Eliminando...');

        axios.post(`${self.apphost}/xoxo/reg_img/eliminar`, {
          id: img.pag_item_img_id
        })
        .then(()=>{

          // 🔥 refrescar lista
          self.verImagenes(self.itemActual);

        })
        .catch(()=>{
          apprise('Error al eliminar');
        })
        .finally(()=>$.unblockUI());

      });

    },

    verImagenes(id){

      this.itemActual = id

      this.bloquear('Cargando...')

      axios.get(`${this.apphost}/xoxo/reg_img/listar`,{
        params:{ id:id }
      })
      .then(r=>{
        this.imagenes = r.data.data || []

        this.$nextTick(()=>{
          this.activarDragImagenes();
        });

        $('#modalImagenes').modal('show')
      })
      .finally(()=>$.unblockUI())

    },

    verVideos(id){

      this.itemActual = id

      this.bloquear('Cargando...')

      axios.get(`${this.apphost}/xoxo/reg_vid/listar`,{
        params:{ id:id }
      })
      .then(r=>{
        this.videos = r.data.data || []
        $('#modalVideos').modal('show')
      })
      .finally(()=>$.unblockUI())

    },    

    eliminarCategoria(id){

      apprise('¿Eliminar?',{confirm:true},ok=>{
        if(!ok)return

        axios.post(`${this.apphost}/xoxo/reg_cat/eliminar`,{cat_pag_web_id:id})
        .then(()=>this.abrirCategorias())
      })

    },

    guardarItem(){

      this.itemForm.contenido = $('#txtContenido').summernote('code')
      this.itemForm.subtitulo_detalle = $('#txtSubtitulo').summernote('code') // 🔥

      const url = this.itemForm.item_pag_web_id
        ? `${this.apphost}/xoxo/reg_item/editar`
        : `${this.apphost}/xoxo/reg_item/crear`

      if(!this.itemForm.subcat){
        return apprise('Selecciona subcategoría')
      }

      this.bloquear('Guardando...')

      axios.post(url,{
        item_pag_web_id: this.itemForm.item_pag_web_id,
        titulo: this.itemForm.titulo,
        clave_txt: this.itemForm.clave_txt,
        precio: this.itemForm.precio,
        stock: this.itemForm.stock,
        contenido: this.itemForm.contenido,
        subtitulo_detalle: this.itemForm.subtitulo_detalle, // 🔥
        subcat_id: this.itemForm.subcat?.subcat_pag_web_id || null
      })
      .then(()=>{
        $('#modalItem').modal('hide')
        this.listar()
      })
      .finally(()=>$.unblockUI())
    },

    actualizarSubcat(){

        if(!this.subcatForm.titulo) return apprise('Escribe el título')
        if(!this.subcatForm.cat) return apprise('Selecciona categoría')

        this.bloquear('Actualizando...')

        axios.post(`${this.apphost}/xoxo/reg_subcat/editar`,{
          subcat_pag_web_id: this.subcatForm.subcat_pag_web_id,
          titulo: this.subcatForm.titulo,
          clave_txt: this.subcatForm.clave_txt,
          cat_id: this.subcatForm.cat.cat_pag_web_id
        })
        .then(()=>{

          $('#modalEditarSubcat').modal('hide')

          setTimeout(()=>{
            this.abrirSubcategorias()
          },200)

        })
        .finally(()=>$.unblockUI())
      },

      abrirEditarCategoria(row){

        this.catForm = {
          cat_pag_web_id: row.cat_pag_web_id,
          titulo: row.titulo,
          clave_txt: row.clave_txt || ''
        }

        $('#modalCategorias').modal('hide')

        setTimeout(()=>{
          $('#modalEditarCategoria').modal('show')
        },200)
      },   

      actualizarCategoria(){

        if(!this.catForm.titulo){
          return apprise('Escribe el título')
        }

        this.bloquear('Actualizando...')

        axios.post(`${this.apphost}/xoxo/reg_cat/editar`,{
          cat_pag_web_id: this.catForm.cat_pag_web_id,
          titulo: this.catForm.titulo,
          clave_txt: this.catForm.clave_txt
        })
        .then(()=>{

          $('#modalEditarCategoria').modal('hide')

          setTimeout(()=>{
            this.abrirCategorias()
          },200)

        })
        .finally(()=>$.unblockUI())
      },         

      abrirCategorias(){      

        this.bloquear('Cargando...')  

        axios.get(`${this.apphost}/xoxo/reg_cat/listar`)
        .then(r=>{  

          this.categorias = r.data.data || []

          this.$nextTick(()=>{

            if(!this.dtCat){

              this.dtCat = $('#tablaCat').DataTable({
                language: (typeof dt_language !== 'undefined' ? dt_language : undefined),
                scrollX: true,
                dom: 'frtip',
                order: [[0,'desc']]
              });

              const self = this

              $('#tablaCat tbody')
                .on('click','.editar-cat',function(){
                  const id = $(this).data('id')
                  const row = self.categorias.find(x=>x.cat_pag_web_id==id)
                  self.abrirEditarCategoria(row)
                })
                .on('click','.eliminar-cat',function(){
                  const id = $(this).data('id')
                  self.eliminarCategoria(id)
                })

            }

            this.dtCat.clear()

            this.categorias.forEach(c=>{

              this.dtCat.row.add([
                c.cat_pag_web_id,
                c.titulo,
                c.clave_txt || '',
                `
                <div class="btn-group">
                  <button class="btn btn-mini btn-primary dropdown-toggle" data-toggle="dropdown">
                    Opciones <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu">
                    <li><a href="#" class="editar-cat" data-id="${c.cat_pag_web_id}">Editar</a></li>
                    <li><a href="#" class="eliminar-cat" data-id="${c.cat_pag_web_id}">Eliminar</a></li>
                  </ul>
                </div>
                `
              ])

            })

            this.dtCat.draw()

            $('#modalCategorias').modal('show')

          })

        })
        .finally(()=>$.unblockUI())

      },   

      abrirEditarSubcat(row){

        this.bloquear('Cargando...')

        axios.get(`${this.apphost}/xoxo/reg_cat/listar`)
        .then(r=>{

          this.categorias = r.data.data || []

          // 🔥 armar form base
          this.subcatForm = {
            subcat_pag_web_id: row.subcat_pag_web_id,
            titulo: row.titulo,
            clave_txt: row.clave_txt || '',
            cat: null
          }

          // 🔥 buscar categoría correcta
          const cat = this.categorias.find(
            c => c.cat_pag_web_id == row.cat_pag_web_id
          )

          if(cat){

            // 🔥 esperar render (CLAVE)
            this.$nextTick(()=>{
              this.subcatForm.cat = cat
            })

          }

          $('#modalSubcategorias').modal('hide')

          setTimeout(()=>{
            $('#modalEditarSubcat').modal('show')
          },200)

        })
        .finally(()=>$.unblockUI())
      },

    abrirSubcategorias(){

      this.bloquear('Cargando...')

      axios.get(`${this.apphost}/xoxo/reg_subcat/listar`)
      .then(r=>{

        this.subcategorias = r.data.data || []

        this.$nextTick(()=>{

          if(!this.dtSubcat){

            this.dtSubcat = $('#tablaSubcat').DataTable({
                language: (typeof dt_language !== 'undefined' ? dt_language : undefined),
                scrollX: true,
                dom: 'frtip',
                order: [[0,'desc']]
              });

            const self = this

            $('#tablaSubcat tbody')
              .on('click','.editar-subcat',function(){
                const id = $(this).data('id')
                const row = self.subcategorias.find(x=>x.subcat_pag_web_id==id)
                self.abrirEditarSubcat(row)
              })
              .on('click','.eliminar-subcat',function(){
                const id = $(this).data('id')
                self.eliminarSubcat(id)
              })

          }

          this.dtSubcat.clear()

          this.subcategorias.forEach(s=>{

            this.dtSubcat.row.add([
              s.subcat_pag_web_id,
              s.titulo,
              s.clave_txt || '',
              s.categoria || '',
              `
              <div class="btn-group">
                <button class="btn btn-warning btn-small dropdown-toggle" data-toggle="dropdown">
                  Opciones <span class="caret"></span>
                </button>
                <ul class="dropdown-menu pull-right">
                  <li><a href="#" class="editar-subcat" data-id="${s.subcat_pag_web_id}">Editar</a></li>
                  <li><a href="#" class="eliminar-subcat" data-id="${s.subcat_pag_web_id}">Eliminar</a></li>
                </ul>
              </div>
              `
            ])

          })

          this.dtSubcat.draw()

          $('#modalSubcategorias').modal('show')

        })

      })
      .finally(()=>$.unblockUI())

    },

    eliminarSubcat(id){

      apprise('¿Eliminar?',{confirm:true},ok=>{
        if(!ok)return

        axios.post(`${this.apphost}/xoxo/reg_subcat/eliminar`,{
          subcat_pag_web_id:id
        })
        .then(()=>this.abrirSubcategorias())
      })

    },    

    abrirCrearCategoria(){

      // 🔥 limpiar formulario
      this.catForm = {
        titulo: ''
      }

      $('#modalCategorias').modal('hide')
      $('#modalCrearCategoria').modal('show')

    },

    guardarCategoria(){

      this.bloquear('Guardando...')

      axios.post(`${this.apphost}/xoxo/reg_cat/crear`, this.catForm)
      .then(()=>{

        const self = this

        $('#modalCrearCategoria').modal('hide')

        $('#modalCrearCategoria').one('hidden', function(){

          self.abrirCategorias()

        })

      })
      .finally(()=>$.unblockUI())

    },

    /* IMAGEN */
    abrirSubirImagen(){

      // 🔥 limpiar preview
      this.preview = null;

      // 🔥 limpiar input file
      const input = document.getElementById('fileImg');
      if (input) input.value = '';

      $('#modalImagenes').modal('hide')
      $('#modalSubirImagen').modal('show')
    },

    previewImg(e){
      this.preview=URL.createObjectURL(e.target.files[0])
    },

    guardarImagen(){

      const file = $('#fileImg')[0].files[0];

      if(!file){
        return apprise('Selecciona una imagen');
      }

      let f = new FormData();
      f.append('file', file);
      f.append('item', this.itemActual);

      // 🔥 BLOQUEAR UI
      this.bloquear('Subiendo imagen...');

      setTimeout(()=>{

        axios.post(`${this.apphost}/xoxo/reg_img/crear`, f)
        .then(()=>{

          $('#modalSubirImagen').modal('hide');
          this.verImagenes(this.itemActual);

        })
        .catch(()=>{
          apprise('Error al subir imagen');
        })
        .finally(()=>$.unblockUI());

      },50);
    },

    /* VIDEO */
    abrirSubirVideo(){

      // 🔥 LIMPIAR FORM COMPLETAMENTE
      this.videoForm = {
        titulo:'',
        codigo:''
      };

      // 🔥 LIMPIAR PREVIEW
      $('#previewVideo').html('');

      $('#modalVideos').modal('hide');

      setTimeout(()=>{
        $('#modalSubirVideo').modal('show');
      },200);

    },
    previewVideo(){
      $('#previewVideo').html(this.videoForm.codigo)
    },

    guardarVideo(){

      if(!this.videoForm.titulo) return apprise('Escribe el título')
      if(!this.videoForm.codigo) return apprise('Ingresa el código embed')

      this.bloquear('Guardando...')

      axios.post(`${this.apphost}/xoxo/reg_vid/crear`,{

        titulo: this.videoForm.titulo,
        codigo: this.videoForm.codigo,

        // 🔥 CLAVE: enviar el item actual
        item: this.itemActual

      })
      .then(()=>{

        $('#modalSubirVideo').modal('hide')

        this.verVideos(this.itemActual)

      })
      .finally(()=>$.unblockUI())

    },

    eliminarVideo(v){

      const self = this;

      apprise('¿Eliminar video?', { confirm:true }, function(ok){

        if(!ok) return;

        self.bloquear('Eliminando...');

        axios.post(`${self.apphost}/xoxo/reg_vid/eliminar`, {
          id: v.pag_item_vid_id // 🔥 ESTE ES EL CAMPO CORRECTO
        })
        .then(()=>{
          self.verVideos(self.itemActual);
        })
        .finally(()=>$.unblockUI());

      });

    },    
    abrirEditarItem(row){

        this.bloquear('Cargando...')

        Promise.all([
          axios.get(`${this.apphost}/xoxo/reg_cat/listar`),
          axios.get(`${this.apphost}/xoxo/reg_subcat/listar`)
        ])
        .then(([catRes, subcatRes])=>{

          this.categorias = catRes.data.data || []
          this.subcategorias = subcatRes.data.data || []

          this.itemForm = {
            item_pag_web_id: row.item_pag_web_id,
            titulo: row.titulo,
            clave_txt: row.clave_txt,
            precio: row.precio,
            stock: row.stock,
            contenido: row.contenido,
            subtitulo_detalle: row.subtitulo_detalle, // 🔥
            cat: null,
            subcat: null
          }

          const sub = this.subcategorias.find(
            s => s.subcat_pag_web_id == row.subcat_pag_web_id
          )

          if(sub){
            const cat = this.categorias.find(
              c => c.cat_pag_web_id == sub.cat_pag_web_id
            )

            if(cat){
              this.itemForm.cat = cat

              this.$nextTick(()=>{
                this.itemForm.subcat = sub
              })
            }
          }

          $('#modalItem').modal('show')

          this.$nextTick(()=>{
            $('#txtContenido').summernote({height:200})
            $('#txtContenido').summernote('code', row.contenido || '')

            // 🔥 NUEVO
            $('#txtSubtitulo').summernote({height:150})
            $('#txtSubtitulo').summernote('code', row.subtitulo_detalle || '')
          })

        })
        .finally(()=>$.unblockUI())
      }

  },

  mounted(){
    this.listar()
  },
  watch:{
    'itemForm.cat'(val){
      this.itemForm.subcat = null
    }
  }  
})
</script>