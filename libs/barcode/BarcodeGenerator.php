<?php

    /**
     * General PHP Barcode Generator
     *
     * @author Casper Bakker - picqer.com
     * Based on TCPDF Barcode Generator
     */

    // Copyright (C) 2002-2015 Nicola Asuni - Tecnick.com LTD
    //
    // This file was part of TCPDF software library.
    //
    // TCPDF is free software: you can redistribute it and/or modify it
    // under the terms of the GNU Lesser General Public License as
    // published by the Free Software Foundation, either version 3 of the
    // License, or (at your option) any later version.
    //
    // TCPDF is distributed in the hope that it will be useful, but
    // WITHOUT ANY WARRANTY; without even the implied warranty of
    // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    // See the GNU Lesser General Public License for more details.
    //
    // You should have received a copy of the License
    // along with TCPDF. If not, see
    // <http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT>.
    //
    // See LICENSE.TXT file for more information.

    include_once __DIR__ . '/Exceptions/UnknownTypeException.php';
    include_once __DIR__ . '/Types/TypeEan13.php';

    abstract class BarcodeGenerator
    {
        const TYPE_EAN_13 = 'EAN13';

        protected function getBarcodeData(string $code, string $type): Barcode
        {
            $barcodeDataBuilder = $this->createDataBuilderForType($type);

            return $barcodeDataBuilder->getBarcodeData($code);
        }

        protected function createDataBuilderForType(string $type)
        {
            switch (strtoupper($type)) {
                case self::TYPE_EAN_13:
                    return new TypeEan13();
            }

            throw new UnknownTypeException();
        }
    }
