<?php

    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class CodigoBarras implements JsonSerializable {

        private $codigoBarrasID;
        private $codigoBarrasNumero;
        private $codigoBarrasFechaCreacion;
        private $codigoBarrasFechaModificacion;
        private $codigoBarrasEstado;

        function __construct(int $codigoBarrasID = -1, string $codigoBarrasNumero = "", $codigoBarrasFechaCreacion = '', 
            $codigoBarrasFechaModificacion = '', bool $codigoBarrasEstado = true) 
        {
            $this->codigoBarrasID = $codigoBarrasID;
            $this->codigoBarrasNumero = $codigoBarrasNumero;
            $this->codigoBarrasFechaCreacion = $codigoBarrasFechaCreacion;
            $this->codigoBarrasFechaModificacion = $codigoBarrasFechaModificacion;
            $this->codigoBarrasEstado = $codigoBarrasEstado;
        }

        function getCodigoBarrasID(): int { return $this->codigoBarrasID; }
        function getCodigoBarrasNumero(): string { return $this->codigoBarrasNumero; }
        function getCodigoBarrasFechaCreacion() { return $this->codigoBarrasFechaCreacion; }
        function getCodigoBarrasFechaModificacion() { return $this->codigoBarrasFechaModificacion; }
        function getCodigoBarrasEstado(): bool { return $this->codigoBarrasEstado; }

        function setCodigoBarrasID(int $codigoBarrasID) { $this->codigoBarrasID = $codigoBarrasID; }
        function setCodigoBarrasNumero(string $codigoBarrasNumero) { $this->codigoBarrasNumero = $codigoBarrasNumero; }
        function setCodigoBarrasFechaCreacion($codigoBarrasFechaCreacion) { $this->codigoBarrasFechaCreacion = $codigoBarrasFechaCreacion; }
        function setCodigoBarrasFechaModificacion($codigoBarrasFechaModificacion) { $this->codigoBarrasFechaModificacion = $codigoBarrasFechaModificacion; }
        function setCodigoBarrasEstado(bool $codigoBarrasEstado) { $this->codigoBarrasEstado = $codigoBarrasEstado; }

        function jsonSerialize() {
            return [
                'ID' => $this->codigoBarrasID,
                'Numero' => $this->codigoBarrasNumero,
                'Creacion' => $this->codigoBarrasFechaCreacion ? Utils::formatearFecha($this->codigoBarrasFechaCreacion) : '',
                'Modificacion' => $this->codigoBarrasFechaModificacion ? Utils::formatearFecha($this->codigoBarrasFechaModificacion) : '',
                'CreacionISO' => $this->codigoBarrasFechaCreacion ? Utils::formatearFecha($this->codigoBarrasFechaCreacion, 'Y-MM-dd') : '',
                'ModificacionISO' => $this->codigoBarrasFechaModificacion ? Utils::formatearFecha($this->codigoBarrasFechaModificacion, 'Y-MM-dd') : '',
                'Estado' => $this->codigoBarrasEstado
            ];
        }

    }

?>