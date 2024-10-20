<?php

    function normalizarTexto(string $texto, bool $senna = false): string {
        // Eliminar acentos usando iconv
        $textoNormalizado = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

        // Convertir a mayúsculas y eliminar espacios adicionales
        $textoNormalizado = strtoupper(trim($textoNormalizado));
        
        // Eliminar caracteres especiales y dejar solo letras, números y espacios
        $textoNormalizado = preg_replace('/[^A-Z0-9\s]/', '', $textoNormalizado);
        $textoNormalizado = preg_replace('/\s+/', ' ', $textoNormalizado);

        // Reemplazar palabras y abreviaturas comunes
        $reemplazos = [
            'URBANIZACION' => 'URB', 'URB.' => 'URB',
            'AVENIDA' => 'AV', 'AV.' => 'AV',
            'CALLE' => 'CL', 'CL.' => 'CL',
            'CARRERA' => 'CRA', 'CRA.' => 'CRA',
            'BARRIO' => 'BR', 'BR.' => 'BR',
            'PLAZA' => 'PLZ', 'PLZ.' => 'PLZ',
            'PARQUE' => 'PQ', 'PQ.' => 'PQ',
            'EDIFICIO' => 'ED', 'ED.' => 'ED',
            'PASEO' => 'PS', 'PS.' => 'PS'
        ];
        $textoNormalizado = str_replace(array_keys($reemplazos), array_values($reemplazos), $textoNormalizado);

        // Eliminar palabras irrelevantes (artículos, preposiciones, etc.)
        if ($senna) {
            $irrelevantes = ['COSTADO', 'FRENTE A', 'DETRAS DE', 'EL', 'LA', 'DE', 'Y', 'A', 'EN', 'CON', 'POR', 'DEL'];
            $textoNormalizado = str_replace($irrelevantes, '', $textoNormalizado);
        }

        // Eliminar espacios adicionales de nuevo tras los reemplazos
        $textoNormalizado = trim(preg_replace('/\s+/', ' ', $textoNormalizado));
        
        return $textoNormalizado;
    }

    $similitud = 0;
    similar_text(
        normalizarTexto("calle urb mira flores casa 42", true), 
        normalizarTexto("Calle Urbanización Miraflores, casa #37", true), 
        $similitud
    );
    echo "Porcentaje de similitud: $similitud\n";
    if ($similitud > 90) { // Porcentaje de similitud
        echo "Los textos son similares.";
    } else {
        echo "Los textos no son similares.";
    }

?>