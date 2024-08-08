<?php

    class Direccion {

        private $direccionID;
        private $direccionProvincia;
        private $direccionCanton;
        private $direccionDistrito;
        private $direccionBarrio;
        private $direccionSennas;
        private $direccionDistancia;
        private $direccionEstado;

        function __construct($direccionProvincia, $direccionCanton, $direccionDistrito, $direccionBarrio = "", 
                                $direccionID = 0, $direccionSennas = "", $direccionDistancia = 0, $direccionEstado = true) {
            $this->direccionID = $direccionID;
            $this->direccionProvincia = $direccionProvincia;
            $this->direccionCanton = $direccionCanton;
            $this->direccionDistrito = $direccionDistrito;
            $this->direccionBarrio = $direccionBarrio;
            $this->direccionSennas = $direccionSennas;
            $this->direccionDistancia = $direccionDistancia;
            $this->direccionEstado = $direccionEstado;
        }

        function getDireccionID() { return $this->direccionID; }
        function getDireccionProvincia() { return $this->direccionProvincia; }
        function getDireccionCanton() { return $this->direccionCanton; }
        function getDireccionDistrito() { return $this->direccionDistrito; }
        function getDireccionBarrio() { return $this->direccionBarrio; }
        function getDireccionSennas() { return $this->direccionSennas; }
        function getDireccionDistancia() { return $this->direccionDistancia; }
        function getDireccionEstado() { return $this->direccionEstado; }

        function setDireccionID($direccionID) { $this->direccionID = $direccionID; }
        function setDireccionProvincia($direccionProvincia) { $this->direccionProvincia = $direccionProvincia; }
        function setDireccionCanton($direccionCanton) { $this->direccionCanton = $direccionCanton; }
        function setDireccionDistrito($direccionDistrito) { $this->direccionDistrito = $direccionDistrito; }
        function setDireccionBarrio($direccionBarrio) { $this->direccionBarrio = $direccionBarrio; }
        function setDireccionSennas($direccionSennas) { $this->direccionSennas = $direccionSennas; }
        function setDireccionDistancia($direccionDistancia) { $this->direccionDistancia = $direccionDistancia; }
        function setDireccionEstado($direccionEstado) { $this->direccionEstado = $direccionEstado; }

    }

?>