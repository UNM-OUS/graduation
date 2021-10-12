<?php

namespace Digraph\Modules\event_commencement;

use Digraph\Modules\ous_digraph_module\Users\NetIDUser;
use Digraph\Modules\ous_event_regalia\SignupWindow as Ous_event_regaliaSignupWindow;
use Formward\Fields\CheckboxList;

class SignupWindow extends Ous_event_regaliaSignupWindow
{
    const DCATS = [
        'Juris Doctor' => 'Doctoral/Terminal',
        'PHD' => 'Doctoral/Terminal',
        'MFA' => 'Doctoral/Terminal',
        'EDD' => 'Doctoral/Terminal',
        'DNP' => 'Doctoral/Terminal',
    ];

    protected $emailVerificationNotified;

    public function signupAllowed(): bool
    {
        if (parent::signupAllowed()) {
            /** @var \Digraph\Users\UserInterface */
            $user = $this->cms()->helper('users')->user();
            if ($user && !($user instanceof NetIDUser)) {
                if ($user['email.verified']) {
                    return true;
                } else {
                    if (!$this->emailVerificationNotified) {
                        $url = $this->cms()->helper('urls')->parse('_user/verify');
                        $this->cms()->helper('notifications')->printError("You must <a href='$url'>verify your email</a> to use \"" . $this->name()."\"");
                        $this->emailVerificationNotified = true;
                    }
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public static function degreeLevel(array $degree): string
    {
        if ($cat = $degree['category']) {
            if ($cat == 'Graduate') {
                // first check for full-name, like Juris Doctor
                $deg = $degree['program'];
                if (@static::DCATS[$deg]) {
                    return static::DCATS[$deg];
                }
                if (substr($deg, 0, 7) == 'Doctor ') {
                    return 'Doctoral/Terminal';
                }
                // then check first word, which will be the abbreviation
                $deg = explode(' ', $degree['program'])[0];
                if (@static::DCATS[$deg]) {
                    return static::DCATS[$deg];
                }
                // return master by default
                return 'Master';
            } else {
                return $cat;
            }
        }
        return '?';
    }

    function formMap(string $action): array
    {
        $map = parent::formMap($action);
        $map['degree_level_limit'] = [
            'label' => 'Limit to degree levels',
            'class' => CheckboxList::class,
            'field' => 'degree_level_limit',
            'weight' => 300,
            'options' => [
                'Post-secondary Certificate' => 'Post-secondary Certificate',
                'Associate' => 'Associate',
                'Bachelor' => 'Bachelor',
                'Master' => 'Master',
                'Doctoral/Terminal' => 'Doctoral/Terminal',
            ],
            'tips' => [
                'Use this field to limit what degree levels are allowed to sign up here',
                'Leave all unchecked to not limit signups to specific degree levels'
            ]
        ];
        return $map;
    }

    public function allUserListUsers(string $query = null)
    {
        if (!$query) {
            $user = $this->cms()->helper('users')->user();
            if ($user) {
                $query = $user['netid'] ?? $this->cms()->helper('users')->userIdentifier();
            } else {
                return null;
            }
        }
        // find results
        $results = [];
        foreach ($this->userLists() as $list) {
            foreach ($list->findAll($query) as $user) {
                if ($this->filterUserListUser($user)) {
                    $results[] = $user;
                }
            }
        }
        // cache and return result
        return $results;
    }

    public function firstUserListUser(string $query = null)
    {
        if (!$query) {
            $user = $this->cms()->helper('users')->user();
            if ($user) {
                $query = $user['netid'] ?? $this->cms()->helper('users')->userIdentifier();
            } else {
                return null;
            }
        }
        foreach ($this->userLists() as $list) {
            if ($user = $list->findFirst($query)) {
                if ($this->filterUserListUser($user)) {
                    return $user;
                }
            }
        }
    }

    protected function filterUserListUser(array $degree): bool
    {
        if (!$this['degree_level_limit']) {
            return true;
        } else {
            return in_array($this->degreeLevel($degree), $this['degree_level_limit']);
        }
    }
}
