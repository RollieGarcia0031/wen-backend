<?php

namespace App\Http;

use App\Model\User;

class Cookie {
    public static function setUser(User $user):void
    {
       $_SESSION['user'] = $user;    
    }

    public static function getUser(): User {
        return $_SESSION['user']; 
    }
}
