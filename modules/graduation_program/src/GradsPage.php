<?php
namespace Digraph\Modules\graduation_program;

use Digraph\DSO\Noun;
use Digraph\Modules\ous_event_management\SignupWindow;
use Digraph\Modules\ous_event_management\UserList;

class GradsPage extends Noun
{
    public function childEdgeType(Noun $child): ?string
    {
        if ($child instanceof UserList) {
            return 'event-program-userlist';
        }
        if ($child instanceof SignupWindow) {
            return 'event-program-signupwindow';
        }
        return null;
    }

    function formMap(string $action): array
    {
        $map = parent::formMap($action);
        $map['gradspage_category'] = [
            'weight' => 200,
            'label' => 'Degree category to display',
            'field' => 'gradspage.category',
            'class' => 'select',
            'required' => true,
            'call' => [
                'options' => [[
                    'Associate' => 'Associate',
                    'Bachelor' => 'Bachelor',
                    'Graduate' => 'Graduate',
                    'Post-secondary Certificate' => 'Post-secondary Certificate'
                ]],
            ],
        ];
        return $map;
    }
}
