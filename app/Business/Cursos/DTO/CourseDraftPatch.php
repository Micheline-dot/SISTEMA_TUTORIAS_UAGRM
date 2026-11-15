<?php

namespace App\Business\Cursos\DTO;

/**
 * A null property means the field was omitted. A FieldChange whose value is
 * null means the field was explicitly sent as null.
 */
final readonly class CourseDraftPatch
{
    public function __construct(
        public ?FieldChange $title = null,
        public ?FieldChange $description = null,
        public ?FieldChange $modality = null,
        public ?FieldChange $totalSeats = null,
        public ?FieldChange $isFree = null,
    ) {
    }
}
