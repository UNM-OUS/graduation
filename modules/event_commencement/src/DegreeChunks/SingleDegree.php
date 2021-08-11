<?php

namespace Digraph\Modules\event_commencement\DegreeChunks;

class SingleDegree extends AbstractDegrees
{
    public function body_complete()
    {
        if ($this->degree()) {
            echo $this->degreesHTML([$this->degree()]);
        } else {
            echo '<em>no degree selected</em>';
        }
    }

    public function degree(): ?array
    {
        return @$this->degrees()[$this->signup[$this->name . '.degree']];
    }

    public function complete(): bool
    {
        return !!$this->signup[$this->name . ".degree_val"];
    }

    public function form_map(): array
    {
        $form = parent::form_map();
        $form['degree'] = [
            'label' => 'Degree to be recognized',
            'tips' => ['Due to time constraints we can only recognize each graduate for a single degree/major. If you have multiple degrees/majors, you must select one you would like to be recognized for.'],
            'field' => $this->name . '.degree',
            'required' => true,
            'class' => 'select',
            'options' => array_map(
                function ($degree) {
                    return implode(', ', [
                        $degree['college'],
                        $degree['program'],
                        $degree['major'],
                    ]);
                },
                $this->degrees()
            ),
        ];
        return $form;
    }

    public function hook_update()
    {
        $name = $this->name;
        $selected = $this->signup["$name.degree"];
        if ($selected && @$this->degrees()[$selected]) {
            $this->signup["$name.degree_val"] = $this->degrees()[$selected];
        }
        parent::hook_update();
    }
}
