<?php

namespace RPMS\App\Security;

use Hautelook\Phpass\PasswordHash;

class PasswordManager
{
    private string $salt;
    private object $hasher;
    private string $pepper;

    public function __construct(string $salt, string $pepper)
    {
        $this->salt   = $salt;
        $this->pepper = $pepper;
        $this->hasher = new PasswordHash(11, false);
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