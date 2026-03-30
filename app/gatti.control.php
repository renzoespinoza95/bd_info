<?php
if (!empty($ssa_id) && is_string($ssa_id)) {
	$tipo_admin = $administrador_actual['rol_nombre'];

	switch ($tipo_admin) {

	    case 'GLOBAL':	    	
	        require_once VARPATH."/public/admin/tab_vari/control.php";
			require_once VARPATH."/public/admin/tab_adm/control.php";
			require_once VARPATH."/public/admin/tab_menu/control.php";
			require_once VARPATH."/public/admin/tab_slider/control.php";
			require_once VARPATH."/public/admin/tab_usu/control.php";
			require_once VARPATH."/public/admin/tab_pag_web/control.php";
			require_once VARPATH."/classes/JWT.class.php";
			require_once VARPATH. "/classes/SimpleImage.class.php";
			require_once VARPATH."/classes/WkHtmlToPdf.php";
			require_once VARPATH."/public/html/control.php";
	        break;

	    case 'NEGOCIO':	        
	        break;

	    case 'ADMIN_PAG_WEB':        
	        break;

	    default:
	        echo "Rol no reconocido";	        
	        break;
	}
}

require_once VARPATH."/public/html/control.php";
