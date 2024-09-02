<?php

    class CodigoBarras {

        private $codigoBarrasID;
        private $codigoBarrasNumero;
        private $codigoBarrasFechaCreacion;
        private $codigoBarrasFechaModificacion;
        private $codigoBarrasEstado;

        function __construct($codigoBarrasNumero, $codigoBarrasID = -1, $codigoBarrasFechaCreacion = '', 
            $codigoBarrasFechaModificacion = '', $codigoBarrasEstado = true) {

            $this->codigoBarrasID = $codigoBarrasID;
            $this->codigoBarrasNumero = $codigoBarrasNumero;
            $this->codigoBarrasFechaCreacion = $codigoBarrasFechaCreacion;
            $this->codigoBarrasFechaModificacion = $codigoBarrasFechaModificacion;
            $this->codigoBarrasEstado = $codigoBarrasEstado;
        }

        function getCodigoBarrasID() { return $this->codigoBarrasID; }
        function getCodigoBarrasNumero() { return $this->codigoBarrasNumero; }
        function getCodigoBarrasFechaCreacion() { return $this->codigoBarrasFechaCreacion; }
        function getCodigoBarrasFechaModificacion() { return $this->codigoBarrasFechaModificacion; }
        function getCodigoBarrasEstado() { return $this->codigoBarrasEstado; }

        function setCodigoBarrasID($codigoBarrasID) { $this->codigoBarrasID = $codigoBarrasID; }
        function setCodigoBarrasNumero($codigoBarrasNumero) { $this->codigoBarrasNumero = $codigoBarrasNumero; }
        function setCodigoBarrasFechaCreacion($codigoBarrasFechaCreacion) { $this->codigoBarrasFechaCreacion = $codigoBarrasFechaCreacion; }
        function setCodigoBarrasFechaModificacion($codigoBarrasFechaModificacion) { $this->codigoBarrasFechaModificacion = $codigoBarrasFechaModificacion; }
        function setCodigoBarrasEstado($codigoBarrasEstado) { $this->codigoBarrasEstado = $codigoBarrasEstado; }

        function __toString() {
            return "CodigoBarras {" . 
                   "ID: " . $this->codigoBarrasID . ", " . 
                   "Número: " . $this->codigoBarrasNumero . ", " . 
                   "Fecha Creación: " . $this->codigoBarrasFechaCreacion . ", " . 
                   "Fecha Modificación: " . $this->codigoBarrasFechaModificacion . ", " . 
                   "Estado: " . ($this->codigoBarrasEstado ? 'Activo' : 'Inactivo') . 
                   "}";
        }

    }

?>