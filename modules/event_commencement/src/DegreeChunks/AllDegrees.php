<?php
namespace Digraph\Modules\event_commencement\DegreeChunks;

class AllDegrees extends AbstractDegrees
{
    protected function buttonText_cancelEdit()
    {
        return "cancel updating";
    }

    protected function buttonText_edit()
    {
        return "";
    }

    protected function buttonText_editIncomplete()
    {
        return "";
    }

    public function body_incomplete()
    {
        $this->body_complete();
    }

    public function body_complete()
    {
        $this->pullDegrees();
        if ($degrees = $this->degrees()) {
            echo $this->degreesHTML($degrees);
        } else {
            echo "<p><em>No degrees found</em></p>";
        }
        echo '<div class="notification notification-notice">Degree records are pulled periodically from Banner. If what you see here isn\'t what you expected, please check first with your academic advisor.</div>';
    }

    protected function onFormHandled()
    {
        unset($this->signup['degrees']);
        $this->signup['degrees'] = $this->degrees();
    }

    public function complete(): bool
    {
        return !!$this->degrees();
    }
}
