<?php
namespace Digraph\Modules\graduation_program;

class Graduate
{
    protected $row;

    public function __construct(array $row)
    {
        $this->row = $row;
    }

    public function hash(): string
    {
        return md5(serialize([
            $this->name(),
            $this->program(),
            $this->semester(),
        ]));
    }

    public function netID(): ?string
    {
        return $this->row['netid'];
    }

    public function college(): string
    {
        return $this->row['college'] ?? 'Unknown college';
    }

    public function honors(): ?string
    {
        return $this->row['honors'];
    }

    public function campus(): string
    {
        if (in_array($this->row['campus'], ['Gallup', 'Taos', 'Valencia', 'Los Alamos'])) {
            return 'UNM&ndash;' . $this->row['campus'];
        } else {
            return $this->row['campus'];
        }
    }

    public function semester(): string
    {
        return $this->row['semester'] ?? 'Unknown semester';
    }

    public function firstName(): string
    {
        return $this->row['first name'] ?? 'Unknown';
    }

    public function lastName(): string
    {
        return $this->row['last name'] ?? 'Unknown';
    }

    public function program(): string
    {
        return $this->row['program'] ?? 'Unknown program';
    }

    public function status(): string
    {
        return $this->row['graduation status'] ?? 'Unknown status';
    }

    public function name(): string
    {
        return $this->firstName() . ' ' . $this->lastName();
    }
}
