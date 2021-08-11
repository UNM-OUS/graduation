<?php
namespace Digraph\Modules\event_commencement\DegreeChunks;

use Digraph\Forms\Fields\FieldValueAutocomplete;
use Digraph\Modules\ous_event_management\Chunks\AbstractChunk;

class CustomDegreeChunk extends AbstractChunk
{
    protected $label = 'Enter a degree manually (admin only)';
    const WEIGHT = 199;

    public function complete(): bool
    {
        return true;
    }

    public function body_complete()
    {
        if ($degree = $this->signup->customDegree()) {
            echo AbstractDegrees::degreesHTML([$degree]);
        }
    }

    public function form_map(): array
    {
        $name = 'degree';
        $types = ['event-signup'];
        return [
            'active' => [
                'label' => 'Activate this custom degree',
                'class' => 'checkbox',
                'field' => "$name.active",
                'weight' => 100,
            ],
            'level' => [
                'label' => 'Degree level/category',
                'class' => 'select',
                'field' => "$name.category",
                'options' => [
                    'Certificate' => 'Certificate',
                    'Associate' => 'Associate',
                    'Bachelor' => 'Bachelor',
                    'Master' => 'Master',
                    'Doctoral' => 'Doctoral',
                ],
                'required' => true,
                'weight' => 100,
            ],
            'college' => [
                'label' => 'School/College',
                'class' => FieldValueAutocomplete::class,
                'field' => "$name.college",
                'extraConstructArgs' => [
                    $types, //types
                    ["$name.degree_val.college"], //fields
                    true, //allow adding
                ],
                'required' => true,
                'weight' => 100,
            ],
            'department' => [
                'label' => 'Department',
                'class' => FieldValueAutocomplete::class,
                'field' => "$name.department",
                'extraConstructArgs' => [
                    $types, //types
                    ["$name.degree_val.department"], //fields
                    true, //allow adding
                ],
                'required' => true,
                'weight' => 100,
            ],
            'program' => [
                'label' => 'Program',
                'class' => FieldValueAutocomplete::class,
                'field' => "$name.program",
                'extraConstructArgs' => [
                    $types, //types
                    ["$name.degree_val.program"], //fields
                    true, //allow adding
                ],
                'required' => true,
                'weight' => 100,
            ],
            'major' => [
                'label' => 'Major 1',
                'class' => FieldValueAutocomplete::class,
                'field' => "$name.major",
                'extraConstructArgs' => [
                    $types, //types
                    ["$name.degree_val.major"], //fields
                    true, //allow adding
                ],
                'required' => true,
                'weight' => 100,
            ],
            'major2' => [
                'label' => 'Major 2',
                'class' => FieldValueAutocomplete::class,
                'field' => "$name.second major",
                'extraConstructArgs' => [
                    $types, //types
                    ["$name.degree_val.second major"], //fields
                    true, //allow adding
                ],
                'weight' => 100,
            ],
            'semester' => [
                'label' => 'Semester',
                'class' => FieldValueAutocomplete::class,
                'field' => "$name.semester",
                'extraConstructArgs' => [
                    $types, //types
                    ["$name.degree_val.semester"], //fields
                    true, //allow adding
                ],
                'required' => true,
                'weight' => 100,
            ],
            'status' => [
                'label' => 'Degree status',
                'class' => 'select',
                'field' => "$name.graduation status",
                'options' => [
                    'Awarded' => 'Awarded',
                    'Pending' => 'Pending',
                    'Sought' => 'Sought',
                ],
                'required' => true,
                'weight' => 100,
            ],
        ];
    }
}
