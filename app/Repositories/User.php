<?php

namespace App\Repositories;

use App\Data\User\SearchData;
use App\Database\Criteria;
use App\Services\AppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class User
{
    public static function byId(int $id): object
    {
        $user = Criteria::byId('ak_user', 'idUser', $id);
        $user->groups = Criteria::table('ak_group')
            ->join('ak_usergroup', 'ak_group.idGroup', '=', 'ak_usergroup.idGroup')
            ->where('ak_usergroup.idUser', $user->idUser)
            ->all();
        $user->memberOf = [];
        foreach ($user->groups as $group) {
            $g = $group->name;
            $user->memberOf[$g] = $g;
        }

        return $user;
    }

    public static function registerLogin(object $user): void
    {
        $user->lastLogin = Carbon::now();
        DB::table('ak_user')
            ->where('idUser', $user->idUser)
            ->update(['lastLogin' => $user->lastLogin]);
    }

    public static function authorize(int $idUser): void
    {
        DB::table('ak_user')
            ->where('idUser', $idUser)
            ->update(['status' => 1]);
    }

    public static function getUserLevel(object $user)
    {
        $userLevel = '';
        $levels = AppService::userLevel();
        foreach ($user->groups as $group) {
            foreach ($levels as $level) {
                if ($group->name == $level) {
                    $userLevel = $level;
                    break 2;
                }
            }
        }

        return $userLevel;
    }

    public static function isAdmin(object $user)
    {
        return in_array('ADMIN', $user->memberOf);
    }

    public static function isManager(object $user)
    {
        return in_array('ADMIN', $user->memberOf) || in_array('MANAGER', $user->memberOf);
    }

    public static function isMemberOf(object $user, string $group)
    {
        return in_array(strtoupper($group), $user->memberOf) || static::isAdmin($user);
    }

    public static function create(object $user): void
    {
        Criteria::create('ak_user', [
            'login' => $user->login,
            'passMD5' => md5($user->login),
            'name' => $user->name,
            'email' => $user->email,
            'lastLogin' => Carbon::now(),
            'idLanguage' => 1,
        ]);
        //        $user->idUser = Criteria::function('user_create(?, ?)', [$user->login, $user->passMD5]);
        //        if (isset($user->auth0IdUser)) {
        //            DB::table("user")
        //                ->where("idUser", $user->idUser)
        //                ->update([
        //                    "lastLogin" => $user->lastLogin,
        //                    'auth0IdUser' => $user->auth0IdUser,
        //                    'email' => $user->email,
        //                    'auth0CreatedAt' => $user->auth0CreatedAt,
        //                    'name' => $user->name
        //                ]);
        //        }
    }

    public static function listToGrid(SearchData $search): array
    {
        $criteria = Criteria::table('user')
            ->join('user_group', 'user.idUser', '=', 'user_group.idUser')
            ->join('group', 'user_group.idGroup', '=', 'group.idGroup')
            ->select('group.idGroup', 'user.idUser', 'user.login', 'user.name')
            ->selectRaw("if(user.status = 1,'authorized','pending') as status")
            ->orderBy('group.idGroup')
            ->orderBy('user.login');
        $criteria->where('group.name', 'startswith', $search?->group);
        $criteria->where(function (Criteria $c) use ($search) {
            $c->where('user.name', 'startswith', $search?->user)
                ->orWhere('user.email', 'startswith', $search?->user)
                ->orWhere('user.login', 'startswith', $search?->user);
        });

        return $criteria->get()->groupBy('idGroup')->toArray();
    }

    public static function listGroupForGrid(string $name = ''): array
    {
        return Criteria::table('group')
            ->select('idGroup', 'name')
            ->where('name', 'startswith', $name)
            ->orderBy('idGroup')
            ->keyBy('idGroup')
            ->all();
    }
}
