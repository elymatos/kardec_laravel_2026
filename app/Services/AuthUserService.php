<?php

namespace App\Services;

use App\Data\LoginData;
use App\Database\Criteria;
use App\Repositories\User;

class AuthUserService
{
    public function auth0Login($userInfo)
    {
        debug($userInfo);
        $userData = (object) [
            // 'auth0IdUser' => $userInfo['user_id'],
            'login' => $userInfo['email'],
            'email' => $userInfo['email'],
            // 'auth0CreatedAt' => $userInfo['created_at'],
            'name' => $userInfo['name'],
            'nick' => $userInfo['nickname'],
        ];
        debug($userData);
        $user = Criteria::one('ak_user', ['email', '=', $userData->email]);
        if (is_null($user)) {
            User::create($userData);

            return 'new';
        } else {
            $user = User::byId($user->idUser);
            User::registerLogin($user);
            $idLanguage = $user->idLanguage;
            if ($idLanguage == '') {
                $idLanguage = config('pk.defaultIdLanguage');
            }
            session(['user' => $user]);
            session(['idLanguage' => $idLanguage]);
            //            session(['userLevel' => User::getUserLevel($user)]);
            session(['isAdmin' => User::isMemberOf($user, 'ADMIN')]);
            //            session(['isMaster' => User::isMemberOf($user, 'MASTER')]);
            //            session(['isAnno' => User::isMemberOf($user, 'ANNO')]);
            debug("[LOGIN] Authenticated {$user->login}");

            return 'logged';
        }
    }

    public function md5Login(LoginData $userInfo)
    {
        $user = Criteria::one('user', ['login', '=', $userInfo->login]);
        if (is_null($user)) {
            User::create((object) [
                'login' => $userInfo->login,
                'passMD5' => $userInfo->password,
            ]);

            return 'new';
        } else {
            if ($user->status == '0') {
                return 'pending';
            } else {
                $user = User::byId($user->idUser);
                if ($user->passMD5 == $userInfo->password) {
                    User::registerLogin($user);
                    $idLanguage = $user->idLanguage;
                    if ($idLanguage == '') {
                        $idLanguage = config('webtool.defaultIdLanguage');
                    }
                    session(['user' => $user]);
                    session(['idLanguage' => $idLanguage]);
                    session(['userLevel' => User::getUserLevel($user)]);
                    session(['isAdmin' => User::isMemberOf($user, 'ADMIN')]);
                    session(['isMaster' => User::isMemberOf($user, 'MASTER')]);
                    session(['isAnno' => User::isMemberOf($user, 'ANNO')]);
                    debug("[LOGIN] Authenticated {$user->login}");

                    return 'logged';
                } else {
                    return 'failed';
                }
            }
        }
    }
}
