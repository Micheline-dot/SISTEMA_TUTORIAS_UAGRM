<?php

namespace App\Business\Cursos\Policies;

use App\Business\Categorias\Contracts\CategoryRepository;
use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseDraftPatch;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\DTO\FieldChange;
use App\Business\Cursos\DTO\InvalidCourseDraftData;

/**
 * CU07: reglas de un borrador. Los campos pueden estar vacíos (se exigen al
 * publicar, CU09), pero lo que se envía debe ser válido.
 */
final class CourseDraftRules
{
    public const MAX_TITLE = 150;
    public const MAX_DESCRIPTION = 5000;

    /** El catálogo de categorías es opcional para poder usar las reglas en pruebas unitarias. */
    public function __construct(private readonly ?CategoryRepository $categories = null)
    {
    }

    public function assertValidNewDraft(CourseDraftData $data): void
    {
        if ($data->totalSeats !== null && $data->totalSeats <= 0) {
            throw new InvalidCourseDraftData('Total seats must be greater than zero.');
        }

        $this->assertLength($data->title, self::MAX_TITLE, 'title');
        $this->assertLength($data->description, self::MAX_DESCRIPTION, 'description');
        $this->assertCategory($data->categoryId);
        $this->assertDates($data->startDate, $data->endDate);
    }

    public function assertValidPatch(CourseDraftPatch $patch): void
    {
        $this->assertStringChange($patch->title, 'title');
        $this->assertStringChange($patch->description, 'description');

        if ($patch->modality !== null && $patch->modality->value !== null
            && ! $patch->modality->value instanceof CourseModality) {
            throw new InvalidCourseDraftData('The provided modality is invalid.');
        }

        if ($patch->totalSeats !== null && $patch->totalSeats->value !== null) {
            if (! is_int($patch->totalSeats->value) || $patch->totalSeats->value <= 0) {
                throw new InvalidCourseDraftData('Total seats must be an integer greater than zero.');
            }
        }

        if ($patch->isFree !== null && $patch->isFree->value !== null
            && ! is_bool($patch->isFree->value)) {
            throw new InvalidCourseDraftData('The provided economic condition is invalid.');
        }

        $this->assertLength($patch->title?->value, self::MAX_TITLE, 'title');
        $this->assertLength($patch->description?->value, self::MAX_DESCRIPTION, 'description');
        $this->assertCategory($patch->categoryId?->value);
        $this->assertDates($patch->startDate?->value, $patch->endDate?->value);
    }

    /** Fecha Y-m-d válida, y fin >= inicio cuando ambas existen. */
    public function assertDates(mixed $start, mixed $end): void
    {
        foreach (['start' => $start, 'end' => $end] as $label => $date) {
            if ($date !== null && ! self::isValidDate($date)) {
                throw new InvalidCourseDraftData("The {$label} date is invalid.");
            }
        }

        if ($start !== null && $end !== null && $end < $start) {
            throw new InvalidCourseDraftData('The end date cannot be before the start date.');
        }
    }

    public static function isValidDate(mixed $date): bool
    {
        if (! is_string($date) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return false;
        }

        [$y, $m, $d] = array_map('intval', explode('-', $date));

        return checkdate($m, $d, $y);
    }

    private function assertCategory(mixed $categoryId): void
    {
        if ($categoryId === null) {
            return;
        }

        if (! is_string($categoryId) || ! ctype_digit($categoryId)
            || ($this->categories !== null && ! $this->categories->exists($categoryId))) {
            throw new InvalidCourseDraftData('The selected category does not exist.');
        }
    }

    private function assertLength(mixed $value, int $max, string $field): void
    {
        if (is_string($value) && mb_strlen($value) > $max) {
            throw new InvalidCourseDraftData("The {$field} cannot exceed {$max} characters.");
        }
    }

    private function assertStringChange(?FieldChange $change, string $field): void
    {
        if ($change !== null && $change->value !== null && ! is_string($change->value)) {
            throw new InvalidCourseDraftData("The provided {$field} must be a string or null.");
        }
    }
}
