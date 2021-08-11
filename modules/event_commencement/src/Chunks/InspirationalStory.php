<?php
namespace Digraph\Modules\event_commencement\Chunks;

use Digraph\Modules\ous_event_management\Chunks\AbstractChunk;

class InspirationalStory extends AbstractChunk
{
    protected $label = "My graduate story";

    public function body_complete()
    {
        echo $this->signup->cms()
            ->helper('filters')
            ->filterContentField(
                $this->signup[$this->name],
                $this->signup['dso.id']
            );
    }

    protected function form_map(): array
    {
        return [
            'story' => [
                'label' => '',
                'field' => $this->name,
                'class' => 'digraph_content_default',
                'required' => true,
            ],
        ];
    }
}
