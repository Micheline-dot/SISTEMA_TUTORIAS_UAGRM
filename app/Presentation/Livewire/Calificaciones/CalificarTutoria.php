<?php

namespace App\Presentation\Livewire\Calificaciones;

use App\Business\Calificaciones\Exceptions\RatingRuleViolation;
use App\Business\Calificaciones\SatisfactionSurvey;
use App\Business\Calificaciones\UseCases\RateTutor;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/** CU16 Calificar tutor y reseña · CU17 Responder formulario. */
final class CalificarTutoria extends Component
{
    public string $courseId;

    public int $puntuacion = 0;

    public string $comentario = '';

    /** @var array<string, string> */
    public array $respuestas = [];

    protected RateTutor $calificar;

    public function boot(RateTutor $calificar): void
    {
        $this->calificar = $calificar;
    }

    public function mount(string $courseId): void
    {
        $this->courseId = $courseId;
    }

    private function estudiante(): string
    {
        return (string) auth()->id();
    }

    public function elegir(int $estrellas): void
    {
        $this->puntuacion = max(1, min(5, $estrellas));
        $this->resetErrorBag('puntuacion');
    }

    public function enviarCalificacion(): void
    {
        $this->validate([
            'puntuacion' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['required', 'string', 'min:'.RateTutor::MIN_COMMENT, 'max:'.RateTutor::MAX_COMMENT],
        ], [
            'puntuacion.min' => 'Elige de 1 a 5 estrellas.',
            'comentario.required' => 'Escribe tu reseña.',
            'comentario.min' => 'La reseña debe tener al menos '.RateTutor::MIN_COMMENT.' caracteres.',
        ]);

        try {
            $this->calificar->rate($this->estudiante(), $this->courseId, $this->puntuacion, $this->comentario);
            session()->flash('ok', '¡Gracias por tu calificación! Ahora responde la breve encuesta.');
        } catch (RatingRuleViolation $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function enviarEncuesta(): void
    {
        $reglas = [];
        foreach (SatisfactionSurvey::QUESTIONS as $clave => $q) {
            $reglas["respuestas.$clave"] = match ($q['type']) {
                'ESCALA' => ['required', 'in:1,2,3,4,5'],
                'SI_NO' => ['required', 'in:SI,NO'],
                default => ['nullable', 'string', 'max:1000'],
            };
        }

        $this->validate($reglas, ['respuestas.*.required' => 'Responde esta pregunta.', 'respuestas.*.in' => 'Responde esta pregunta.']);

        $datos = [];
        foreach (SatisfactionSurvey::QUESTIONS as $clave => $q) {
            $valor = $this->respuestas[$clave] ?? null;
            $datos[$clave] = $q['type'] === 'ESCALA' ? (int) $valor : $valor;
        }

        try {
            $this->calificar->answerSurvey($this->estudiante(), $this->courseId, $datos);
            session()->flash('ok', '¡Gracias! Tu opinión ayuda a mejorar las tutorías.');
        } catch (RatingRuleViolation $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function render(): View
    {
        $estado = $this->calificar->status($this->estudiante(), $this->courseId);

        abort_if($estado['courseTitle'] === null, 404);

        return view('livewire.calificaciones.calificar-tutoria', [
            'estado' => $estado,
            'preguntas' => SatisfactionSurvey::QUESTIONS,
            'escala' => SatisfactionSurvey::SCALE_LABELS,
        ])->layout('layouts.student')->title('Calificar tutoría');
    }
}
