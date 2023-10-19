<!--

function op(v1,v2){
	open(v1,"_blank",v2);
}

function op2(v1){
	param = 'toolbar=no,location=no,directories=no,status=no,scrollbars=yes,menubar=yes,resizable=yes,width=800,height=500,top=5,left=20';
	open(v1,"_blank",param);
}

function SetHelp(txt) { //help.innerText = txt ; }
}

function setLocation(url){
    window.location.href = url;
}

function FormataHora(campo,teclapres) {
	var tecla = (window.Event) ? teclapres.which : teclapres.keyCode;
	vr = document.form[campo].value;
	vr = vr.replace( ".", "" );
	vr = vr.replace( ":", "" );
	vr = vr.replace( "/", "" );
	tam = vr.length + 1;
	
	if (tecla == 8 || tecla == 0 ){
		return true;
	}

	if (tam == 1){
		if (tecla < 48 || tecla > 50){
			return false;
		}
	}
	
	if (tam == 2){
		ant = vr.substr( 0, 1 );
		if (ant == '2' && (tecla < 48 || tecla > 52)){
			return false;
		}
	}
	
	if (tam == 3){
		if (tecla < 48 || tecla > 53){
			return false;
		}
	}
	
	if ( tecla != 9 && tecla != 8 ){
		if ( tam > 2 && tam < 5 )
			document.form[campo].value = vr.substr( 0, tam - 2  ) + ':' + vr.substr( tam - 2, tam );
			return true;
	}
	return false;
}
//-->
