<?php

namespace App\Http;

use App\Model\User;

class Cookie {
    public static function setUser(User $user):void
    {
       $_SESSION['user'] = $user;    
    }

    public static function getUser(): User | null {
        if (!isset($_SESSION['user'])) {
            return null;
        }
        $user = $_SESSION['user'];
        return  $user;
    }
}
