<?php

namespace App\Business\Cursos\Policies;

use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseDraftPatch;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\DTO\FieldChange;
use App\Business\Cursos\DTO\InvalidCourseDraftData;

final class CourseDraftRules
{
    public function assertValidNewDraft(CourseDraftData $data): void
    {
        if ($data->totalSeats !== null && $data->totalSeats <= 0) {
            throw new InvalidCourseDraftData('Total seats must be greater than zero.');
        }
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
    }

    private function assertStringChange(?FieldChange $change, string $field): void
    {
        if ($change !== null && $change->value !== null && ! is_string($change->value)) {
            throw new InvalidCourseDraftData("The provided {$field} must be a string or null.");
        }
    }
}
