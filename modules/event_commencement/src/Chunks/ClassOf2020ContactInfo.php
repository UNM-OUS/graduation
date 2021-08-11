<?php
namespace Digraph\Modules\event_commencement\Chunks;

use Digraph\Modules\ous_event_management\Chunks\Contact\MailingContactInformation;

class ClassOf2020ContactInfo extends MailingContactInformation
{
    protected function form_map(): array
    {
        $map = parent::form_map();
        $map['mailingaddress']['required'] = false;
        return $map;
    }
}
