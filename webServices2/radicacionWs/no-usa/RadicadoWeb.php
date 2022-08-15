<?php
	class RadicadoWeb {
		var $secuencia;
		var $db;

		function RadicadoWeb ($db) {
			$this->db = $db;
		}

		function radicar($radicado = null, $usuario, $depedencia)  {
			$sql = "INSERT INTO RADICADO (RADI_NUME_RADI,	
							RADI_FECH_RADI,
							TDOC_CODI,
							TRTE_CODI,
							MREC_CODI,
							EESP_CODI,
							EOTRA_CODI,
							RADI_TIPO_EMPR,
							RADI_FECH_OFIC,
							TDID_CODI,
							RADI_NUME_IDEN,
							RADI_NOMB,
							RADI_PRIM_APEL,
							RADI_SEGU_APEL,
							RADI_PAIS,
							MUNI_CODI,
							CPOB_CODI,
							CARP_CODI,
							ESTA_CODI,
							DPTO_CODI,	
							CEN_MUNI_CODI,
							CEN_DPTO_CODI,	
							RADI_DIRE_CORR,	
							RADI_TELE_CONT,	
							RADI_NUME_HOJA,
							RADI_DESC_ANEX,
							RADI_NUME_DERI,
							RADI_PATH,
							RADI_USUA_ACTU,
							RADI_DEPE_ACTU,
							RADI_FECH_ASIG,
							RADI_ARCH1,
							RADI_ARCH2,
							RADI_ARCH3,
							RADI_ARCH4,
							RA_ASUN,
							RADI_USU_ANTE,
							RADI_DEPE_RADI,
							RADI_REM,
							RADI_USUA_RADI,
							CODI_NIVEL,
							FLAG_NIVEL,
							CARP_PER,
							RADI_LEIDO,
							RADI_CUENTAI,
							RADI_TIPO_DERI,
							LISTO,
							SGD_TMA_CODIGO,
							SGD_MTD_CODIGO,
							PAR_SERV_SECUE,
							SGD_FLD_CODIGO,
							RADI_AGEND,
							RADI_FECH_AGEND,
							RADI_FECH_DOC,
							SGD_DOC_SECUENCIA,
							SGD_PNUFE_CODI,
							SGD_EANU_CODIGO,
							SGD_TDEC_CODIGO,
							SGD_APLI_CODI,
							USUA_DOC_ANTE,
							USUA_DOC_ACTU,
							SGD_TRAD_CODIGO,
							USUA_FECH_ANTETX,
							SGD_TTR_CODIGO,
							SGD_FECH_ANTETX,
							FECH_VCMTO,
							TDOC_VCMTO,
							SGD_SPUB_CODIGO,
							ID_PAIS,
							ID_CONT) VALUES ()";
		}
