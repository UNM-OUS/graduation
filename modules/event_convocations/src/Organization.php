<?php
namespace Digraph\Modules\event_convocations;

use Digraph\DSO\Noun;
use Digraph\Forms\Fields\FieldValueAutocomplete;
use Formward\Fields\Email;
use Formward\Fields\Phone;
use Formward\Fields\Url;

class Organization extends Noun
{
    protected $events;

    public function events(): array
    {
        if ($this->events === null) {
            $this->events = $this->cms()->helper('graph')->children(
                $this['dso.id'],
                'convocation-org-event',
                1,
                '${dso.modified.date} desc'
            );
        }
        return $this->events;
    }

    public function contactCard(): string
    {
        $out = '';
        if ($this['website'] || $this['email'] || $this['phone']) {
            $s = $this->cms()->helper('strings');
            $out .= "<div class='digraph-card'>";
            $out .= "<p><strong>Contact</strong> : ";
            $out .= implode(' | ', array_filter([
                $this['email'] ? $s->obfuscate('<a href="mailto:' . $this['email'] . '">' . $this['email'] . '</a>') : null,
                $this['phone'] ? $s->obfuscate('<a href="tel:' . preg_replace('/[^0-9]/', '', $this['phone']) . '">' . $this['phone'] . '</a>') : null,
                $this['website'] ? '<a href="' . $this['website'] . '">' . $this->name() . ' website</a>' : null,
            ]));
            $out .= "</p>";
            $out .= "</div>";
        }
        return $out;
    }

    public function permissions(string $verb, ?string $user): ?bool
    {
        if (in_array($verb, ['edit', 'manage', 'add-convocation'])) {
            list($id, $manager) = explode('@', $user);
            if ($manager == 'netid') {
                foreach ($this->coordinators() as $c) {
                    if ($id == $c['netid']) {
                        return true;
                    }
                }
            }
        }
        return null;
    }

    public function coordinators(): array
    {
        return $this->cms()->helper('graph')->children($this['dso.id'], 'convocation-coordinator');
    }

    public function formMap(string $action): array
    {
        $map = parent::formMap($action);
        $map['digraph_title'] = false;
        $map['digraph_body'] = false;
        $map['website'] = [
            'label' => 'Website',
            'field' => 'website',
            'class' => Url::class,
            'required' => false,
            'weight' => 500,
        ];
        $map['email'] = [
            'label' => 'Email',
            'field' => 'email',
            'class' => Email::class,
            'required' => false,
            'weight' => 501,
        ];
        $map['phone'] = [
            'label' => 'Phone',
            'field' => 'phone',
            'class' => Phone::class,
            'required' => false,
            'weight' => 502,
        ];
        $map['category'] = [
            'label' => 'Category',
            'field' => 'category',
            'class' => FieldValueAutocomplete::class,
            'extraConstructArgs' => [
                ['convocation-org'], //types
                ['category'], //fields
                in_array('admin', $this->cms()->helper('users')->groups()), //allow adding
            ],
            'default' => 'Convocations',
            'required' => true,
            'weight' => 900,
        ];
        return $map;
    }
}
