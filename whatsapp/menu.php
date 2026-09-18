<?php
declare(strict_types=1);

function menuWhatsApp(string $tipoUsuario): string
{
    if ($tipoUsuario === 'afiliado') {
        return "¿Qué deseas consultar?\n\n"
            . "1️⃣ Mi plan\n"
            . "2️⃣ Mis servicios\n"
            . "3️⃣ Citas médicas\n"
            . "4️⃣ Clínicas\n"
            . "5️⃣ Especialistas\n"
            . "6️⃣ Hablar con un asesor\n\n"
            . "Escribe el número de tu opción.";
    }

    return "¿Qué deseas conocer?\n\n"
        . "1️⃣ Conocer planes\n"
        . "2️⃣ Conocer servicios\n"
        . "3️⃣ Beneficios\n"
        . "4️⃣ Quiero afiliarme\n"
        . "5️⃣ Hablar con un asesor\n\n"
        . "Escribe el número de tu opción.";
}

function normalizarComandoWhatsApp(string $texto): string
{
    $texto = trim($texto);
    $texto = strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);
    return strtoupper($texto);
}

function respuestaMenuAfiliado(string $opcion): ?string
{
    return match ($opcion) {
        '1' => "📋 *Mi plan*\n\nLa consulta detallada de cobertura aún debe conectarse a la fuente oficial autorizada por Coomeva. Escribe 6 si deseas hablar con un asesor.\n\nEscribe MENU para volver.",
        '2' => "🏥 *Mis servicios*\n\nLa información definitiva de servicios se cargará únicamente desde el catálogo oficial validado por Coomeva. Escribe 6 si necesitas orientación.\n\nEscribe MENU para volver.",
        '3' => "📅 *Citas médicas*\n\nLa solicitud, cancelación y reprogramación de citas aún no están habilitadas en este canal. Un asesor puede orientarte.\n\nEscribe 6 para solicitar asesor o MENU para volver.",
        '4' => "🏨 *Clínicas*\n\nLa red de clínicas se mostrará cuando Coomeva entregue y autorice la fuente oficial para este canal.\n\nEscribe MENU para volver.",
        '5' => "👨‍⚕️ *Especialistas*\n\nLa disponibilidad de especialistas requiere integración con la información oficial. No compartas diagnósticos ni documentos médicos por este chat.\n\nEscribe MENU para volver.",
        default => null,
    };
}

function respuestaMenuNoAfiliado(string $opcion): ?string
{
    return match ($opcion) {
        '1' => "📋 *Planes Coomeva*\n\nContamos con opciones individuales y familiares. Las condiciones finales se confirmarán con la información comercial oficial.\n\nEscribe 4 o 5 para hablar con un asesor, o MENU para volver.",
        '2' => "🏥 *Servicios*\n\nEl catálogo de servicios se publicará cuando sea validado por Coomeva. Un asesor puede ayudarte a conocer las alternativas disponibles.\n\nEscribe 5 para solicitar asesor o MENU para volver.",
        '3' => "✨ *Beneficios*\n\nLos beneficios, coberturas y condiciones se comunicarán solo con contenido comercial aprobado por Coomeva.\n\nEscribe 4 o 5 para hablar con un asesor, o MENU para volver.",
        default => null,
    };
}
