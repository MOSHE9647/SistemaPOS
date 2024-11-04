<?php

    require_once dirname(__DIR__, 1) . '/utils/Utils.php';

    class CodigoBarras implements JsonSerializable {

        private $codigoBarrasID;
        private $codigoBarrasNumero;
        private $codigoBarrasEstado;

        function __construct(int $codigoBarrasID = -1, string $codigoBarrasNumero = "", bool $codigoBarrasEstado = true) 
        {
            $this->codigoBarrasID = $codigoBarrasID;
            $this->codigoBarrasNumero = $codigoBarrasNumero;
            $this->codigoBarrasEstado = $codigoBarrasEstado;
        }

        function getCodigoBarrasID(): int { return $this->codigoBarrasID; }
        function getCodigoBarrasNumero(): string { return $this->codigoBarrasNumero; }
        function getCodigoBarrasEstado(): bool { return $this->codigoBarrasEstado; }

        function setCodigoBarrasID(int $codigoBarrasID) { $this->codigoBarrasID = $codigoBarrasID; }
        function setCodigoBarrasNumero(string $codigoBarrasNumero) { $this->codigoBarrasNumero = $codigoBarrasNumero; }
        function setCodigoBarrasEstado(bool $codigoBarrasEstado) { $this->codigoBarrasEstado = $codigoBarrasEstado; }

        public static function fromArray(array $codigoBarras): CodigoBarras {
            return new CodigoBarras(
                $codigoBarras['ID'] ?? -1,
                $codigoBarras['Numero'] ?? "",
                $codigoBarras['Estado'] ?? true
            );
        }

        function jsonSerialize() {
            return [
                'ID' => $this->codigoBarrasID,
                'Numero' => $this->codigoBarrasNumero,
                'Estado' => $this->codigoBarrasEstado
            ];
        }

    }

?>