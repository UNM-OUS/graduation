<?php
namespace Digraph\Modules\event_convocations;

use Digraph\DSO\Noun;
use Digraph\Forms\Fields\FieldValueAutocomplete;
use Digraph\Forms\Fields\Noun as FieldsNoun;
use Digraph\Modules\ous_event_regalia\Event;

class Convocation extends Event
{
    const SLUG_ENABLED = false;
    const PRIMARY_EVENT = false;
    protected $organization;

    public function category(): ?string
    {
        if ($this['category']) {
            return $this['category'];
        }
        if ($this->organization()) {
            if ($this->organization()['category']) {
                return $this->organization()['category'];
            }
        }
        return 'Convocations';
    }

    public function parentUrl($verb = 'display')
    {
        if ($verb == 'display') {
            if ($this->eventGroup()) {
                return $this->eventGroup()->url(
                    'secondary-events'
                );
            }
        }
        return parent::parentUrl();
    }

    public function hook_postAddUrl()
    {
        return $this->url()->string();
    }

    public function parentEdgeType(Noun $parent): ?string
    {
        if ($parent instanceof Organization) {
            return 'convocation-org-event';
        }
        return parent::parentEdgeType($parent);
    }

    public function permissions(string $verb, ?string $user): ?bool
    {
        if ($user && in_array($verb, ['edit'])) {
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
        return $this->organization()->coordinators();
    }

    public function organization(): ?Organization
    {
        if (!$this->organization) {
            $ps = $this->cms()->helper('graph')->parents($this['dso.id'], 'convocation-org-event');
            $this->organization = array_shift($ps);
        }
        return $this->organization;
    }

    public function formMap(string $action): array
    {
        $map = parent::formMap($action);
        if ($action == 'add') {
            $search = $this->cms()->factory()->search();
            $search->where('${dso.type} = "event-group" AND CAST(${eventgroup.launchtime} AS int) < :time');
            $search->order('${eventgroup.launchtime} desc');
            $search->limit(1);
            $search->offset(0);
            $result = $search->execute(['time' => time()]);
            if ($result) {
                $defaultGroup = array_pop($result)['dso.id'];
            }
            $map['eventgroup'] = [
                'label' => 'Associated commencement group',
                'field' => 'eventgroup',
                'class' => FieldsNoun::class,
                'call' => [
                    'limitTypes' => [['event-group']],
                ],
                'weight' => 0,
                'required' => true,
                'default' => $defaultGroup,
                'tips' => [
                    'This is set to the current latest/upcoming event by default. Generally you should not need to change it unless you are setting up events in advance for future semesters.',
                    'Once set this field cannot be changed.',
                ],
            ];
        }
        if (in_array('editor', $this->cms()->helper('users')->groups())) {
            $map['category'] = [
                'label' => 'Category',
                'field' => 'category',
                'class' => FieldValueAutocomplete::class,
                'extraConstructArgs' => [
                    ['convocation', 'convocation-org'], //types
                    ['category'], //fields
                    in_array('admin', $this->cms()->helper('users')->groups()), //allow adding
                ],
                'tips' => [
                    'Editor-only field',
                    'Leave this blank to use the organization\'s default category.',
                ],
                'weight' => 800,
            ];
        }
        $map['event_form_preset'] = false;
        $map['digraph_title'] = false;
        $map['digraph_name']['tips'][] = 'The standard format is to name convocations in a way that includes both the semester and college/department name, something like "Spring 2021 College of Fine Arts Convocation"';
        $map['digraph_body']['class'] = 'digraph_content_default';
        $map['digraph_body']['label'] = 'Page content';
        $map['digraph_body']['call']['extra'] = [[]];
        $map['digraph_body']['tips'][] = 'Content to be included on the event\'s public page. Should include any special information about regalia requirements, or any other special instructions your attendees should know about.';
        return $map;
    }
}
