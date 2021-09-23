<?php

namespace Digraph\Modules\deactivated_netid_signin;

use Digraph\Helpers\AbstractHelper;
use PDO;

class DeactivatedNetidHelper extends AbstractHelper
{
    public function pdo(): PDO
    {
        return $this->cms->pdo('oldnetids');
    }

    public function message(string $netid_or_email): ?string
    {
        if (!$this->exists($netid_or_email)) {
            $this->cms->helper('notifications')->error('No matching records found');
            return null;
        }
        $query = $this->pdo()->prepare('SELECT * FROM token WHERE ');
        return $email;
    }

    public function exists(string $netid_or_email): bool
    {
        $query = $this->pdo()->prepare('SELECT * FROM user WHERE netid = :q OR email = :q');
        $query->execute(['q' => strtolower($netid_or_email)]);
        return !!$query->rowCount();
    }

    public function emails(string $netid): array
    {
        $query = $this->pdo()->prepare('SELECT * FROM user WHERE netid = :q');
        $query->execute(['q' => strtolower($netid)]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function netid(string $email): ?string
    {
        $query = $this->pdo()->prepare('SELECT * FROM user WHERE email = :q');
        $query->execute(['q' => strtolower($email)]);
        if ($query->rowCount()) {
            return $query->fetch()['email'];
        } else {
            return null;
        }
    }
}
