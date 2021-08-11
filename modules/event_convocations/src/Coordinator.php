<?php
namespace Digraph\Modules\event_convocations;

use Digraph\DSO\Noun;
use Digraph\Modules\ous_digraph_module\Fields\NetID;
use Formward\Fields\Email;
use Formward\Fields\Phone;

class Coordinator extends Noun
{
    public function parentEdgeType(Noun $parent): ?string
    {
        if ($parent instanceof Organization) {
            return 'convocation-coordinator';
        }
        return null;
    }

    public function formMap(string $action): array
    {
        $map = parent::formMap($action);
        $map['digraph_title'] = false;
        $map['digraph_body'] = false;
        $map['netid'] = [
            'label' => 'NetID',
            'field' => 'netid',
            'class' => NetID::class,
            'required' => true,
            'weight' => 500,
        ];
        $map['email'] = [
            'label' => 'Email',
            'field' => 'email',
            'class' => Email::class,
            'required' => true,
            'weight' => 501,
        ];
        $map['phone'] = [
            'label' => 'Phone',
            'field' => 'phone',
            'class' => Phone::class,
            'required' => false,
            'weight' => 502,
        ];
        return $map;
    }
}
