<?php

namespace App\Fixtures\Providers;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// prope service -> methode hashPassword
// 这里关联的是在Userfixtures.yaml里的内容
class UserProvider
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {
    }

    // retourne string -> password
    public function hashUserPassword(string $plainPassword): string
    {
        // 返回的是一个新的user跟一个plainPassword
        return $this->hasher->hashPassword(new User(), $plainPassword);
    }
}
