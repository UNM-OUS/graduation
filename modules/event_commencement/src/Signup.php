<?php

namespace Digraph\Modules\event_commencement;

use Digraph\Modules\event_commencement\DegreeChunks\AbstractDegrees;
use Digraph\Modules\event_commencement\DegreeChunks\CustomDegreeChunk;
use Digraph\Modules\ous_event_regalia\Signup as Ous_event_regaliaSignup;

class Signup extends Ous_event_regaliaSignup
{
    const DCATS = [
        'Juris Doctor' => 'Doctoral/Terminal',
        'PHD' => 'Doctoral/Terminal',
        'MFA' => 'Doctoral/Terminal',
        'EDD' => 'Doctoral/Terminal',
        'DNP' => 'Doctoral/Terminal',
    ];

    protected function myChunks(): array
    {
        $chunks = parent::myChunks();
        /** @var \Digraph\Permissions\PermissionsHelper */
        $p = $this->cms()->helper('permissions');
        if ($p->check('signup/customdegree', 'events')) {
            $chunks['customdegree'] = CustomDegreeChunk::class;
        }
        return $chunks;
    }

    public function degreeCategory()
    {
        if ($cat = $this['degree.degree_val.category']) {
            if ($cat == 'Graduate') {
                // first check for full-name, like Juris Doctor
                $deg = $this['degree.degree_val.program'];
                if (@static::DCATS[$deg]) {
                    return static::DCATS[$deg];
                }
                if (substr($deg, 0, 7) == 'Doctor ') {
                    return 'Doctoral/Terminal';
                }
                // then check first word, which will be the abbreviation
                $deg = explode(' ', $this['degree.degree_val.program'])[0];
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

    public function customDegree(): ?array
    {
        if (!$this['customdegree.active']) {
            return null;
        }
        $degree = $this['customdegree'];
        unset($degree['chunk']);
        if ($contact = $this->contactInfo()) {
            $degree['name'] = $contact->name();
        }
        return $degree;
    }

    public function degrees(): ?AbstractDegrees
    {
        foreach ($this->chunks() as $chunk) {
            if ($chunk instanceof AbstractDegrees) {
                return $chunk;
            }
        }
        return null;
    }
}
