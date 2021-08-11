<?php
namespace Digraph\Modules\event_commencement\Chunks;

use Digraph\Modules\ous_event_management\Chunks\Contact\ContactInformation;

class GraduationContactInfo extends ContactInformation
{
    public function instructions(): ?string
    {
        if ($this->signup->personalizedPage()) {
            return '<div class="digraph-card">You can change how your name will be read at ceremonies, or on personalized graduate pages, but in printed or online programs your name from Banner will be used.</div>';
        } else {
            return '<div class="digraph-card">You can change how your name will be read at ceremonies, but in printed or online programs your name from Banner will be used.</div>';
        }
    }

    public function body_complete()
    {
        if (!$this->signup[$this->name . '.pronunciation']) {
            $this->signup->cms()->helper('notifications')->printNotice(
                'If you are worried your name might be pronounced incorrectly, please enter guidance for pronouncing it in this section.'
            );
        }
        parent::body_complete();
    }

    protected function form_map(): array
    {
        $map = parent::form_map();
        $map['pronunciation'] = [
            'label' => 'Name pronunciation',
            'field' => $this->name . '.pronunciation',
            'class' => 'text',
            'weight' => 120,
            'required' => false,
            'tips' => [
                'If you are worried your name might be pronounced incorrectly, please enter guidance for pronouncing it.',
                'Example: Low-Bow Loo-See',
            ],
        ];
        return $map;
    }
}
