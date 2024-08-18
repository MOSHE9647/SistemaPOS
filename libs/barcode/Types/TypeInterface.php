<?php

    include_once __DIR__ . '/../Barcode.php';

    interface TypeInterface
    {
        public function getBarcodeData(string $code): Barcode;
    }