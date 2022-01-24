<?php

namespace Digraph\Modules\event_commencement;

use Digraph\Modules\event_commencement\DegreeChunks\AbstractDegrees;
use Digraph\Modules\event_commencement\DegreeChunks\CustomDegreeChunk;
use Digraph\Modules\ous_event_regalia\Signup as Ous_event_regaliaSignup;

class Signup extends Ous_event_regaliaSignup
{

    // protected function myChunks(): array
    // {
    //     $chunks = parent::myChunks();
    //     /** @var \Digraph\Permissions\PermissionsHelper */
    //     $p = $this->cms()->helper('permissions');
    //     if ($p->check('signup/customdegree', 'events')) {
    //         $chunks['customdegree'] = CustomDegreeChunk::class;
    //     }
    //     return $chunks;
    // }

    public function degreeCategory()
    {
        if ($this['degree.degree_val']) {
            return SignupWindow::degreeLevel($this['degree.degree_val']);
        } else {
            return 'Unknown';
        }
    }

    // public function customDegree(): ?array
    // {
    //     if (!$this['customdegree.active']) {
    //         return null;
    //     }
    //     $degree = $this['customdegree'];
    //     unset($degree['chunk']);
    //     if ($contact = $this->contactInfo()) {
    //         $degree['name'] = $contact->name();
    //     }
    //     return $degree;
    // }

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
