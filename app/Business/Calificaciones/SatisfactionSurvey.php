<?php

namespace App\Business\Calificaciones;

/**
 * CU17: encuesta de satisfacción que el estudiante responde después de
 * calificar. Tipos: ESCALA (1–5), SI_NO y TEXTO (opcional).
 */
final class SatisfactionSurvey
{
    public const TITLE = 'Encuesta de satisfacción de la tutoría';

    /** @var array<string, array{text: string, type: string, required: bool}> */
    public const QUESTIONS = [
        'claridad' => ['text' => '¿El tutor explicó los temas con claridad?', 'type' => 'ESCALA', 'required' => true],
        'puntualidad' => ['text' => '¿El tutor fue puntual y respetuoso?', 'type' => 'ESCALA', 'required' => true],
        'material' => ['text' => '¿El material y los recursos fueron útiles?', 'type' => 'ESCALA', 'required' => true],
        'recomienda' => ['text' => '¿Recomendarías esta tutoría a otros estudiantes?', 'type' => 'SI_NO', 'required' => true],
        'mejoras' => ['text' => '¿Qué podría mejorar el tutor? (opcional)', 'type' => 'TEXTO', 'required' => false],
    ];

    public const SCALE_LABELS = [1 => 'Muy en desacuerdo', 2 => 'En desacuerdo', 3 => 'Neutral', 4 => 'De acuerdo', 5 => 'Muy de acuerdo'];
}
