<?php
declare(strict_types=1);

namespace App\Security;

use Hautelook\Phpass\PasswordHash;
use App\Security\ImmutableVariable;

class PasswordManager
{
    private string $salt;
    private object $hasher;
    private string $pepper;

    public function __construct(string $salt)
    {
        $this->salt   = $salt;
        $this->hasher = new PasswordHash(11, false);
        $this->pepper = ImmutableVariable::getValue('passwordPepper');
    }

    public function hashPassword(string $password) : string
    {
        $combinedPassword =  $this->salt . $password .$this->pepper;
        $hash = $this->hasher->HashPassword($combinedPassword);
        return $hash;
    }

    public function verifyPassword(string $password, string $storedHash) : bool
    {
        return $this->hasher->CheckPassword($this->salt . $password . $this->pepper, $storedHash);
    }
}