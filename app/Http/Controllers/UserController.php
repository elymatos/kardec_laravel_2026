<?php

namespace App\Http\Controllers;

use App\Data\ComboBox\QData;
use App\Data\User\CreateData;
use App\Data\User\SearchData;
use App\Data\User\UpdateData;
use App\Database\Criteria;
use App\Repositories\Group;
use App\Repositories\User;
use App\Services\AppService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Put;
use Illuminate\Support\Facades\App;

#[Middleware('web')]
class UserController extends Controller
{
    #[Get(path: '/user')]
    public function resource()
    {
        return view('User.resource');
    }

    #[Get(path: '/user/new')]
    public function new()
    {
        return view('User.formNew');
    }

    #[Get(path: '/user/grid/{fragment?}')]
    #[Post(path: '/user/grid/{fragment?}')]
    public function grid(SearchData $search, ?string $fragment = null)
    {
        debug($search);
        $users = User::listToGrid($search);
        // debug($users);
        $groups = array_filter(
            User::listGroupForGrid($search?->group ?? ''),
            fn ($key) => isset($users[$key]),
            ARRAY_FILTER_USE_KEY
        );
        $view = view('User.grid', [
            'groups' => $groups,
            'users' => $users,
        ]);

        return is_null($fragment) ? $view : $view->fragment('search');
    }

    #[Get(path: '/user/{id}/edit')]
    public function edit(string $id)
    {
        debug($id);

        return view('User.edit', [
            'user' => User::byId($id),
        ]);
    }

    #[Get(path: '/user/{id}/formEdit')]
    public function formEdit(string $id)
    {
        return view('User.formEdit', [
            'user' => User::byId($id),
        ]);
    }

    #[Put(path: '/user/{id}/authorize')]
    public function authorizeUser(string $id)
    {
        try {
            User::authorize($id);
            $this->trigger('reload-gridUser');

            return $this->renderNotify('success', 'User authorized.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Post(path: '/user')]
    public function update(UpdateData $data)
    {
        try {
            // User::update($data);
            Criteria::function('user_update(?)', [$data->toJson()]);
            $this->trigger('reload-gridUser');

            return $this->renderNotify('success', 'User updated.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Post(path: '/user/new')]
    public function create(CreateData $user)
    {
        try {
            $user->groups = [Group::getById($user->idGroup)];
            $user->passMD5 = md5(config('webtool.defaultPassword'));
            User::create($user);
            $this->trigger('reload-gridUser');

            return $this->renderNotify('success', 'User created.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/user/{id}')]
    public function delete(string $id)
    {
        try {
            // User::delete($id);
            Criteria::function('user_delete(?)', [$id]);

            return $this->clientRedirect('/user');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Get(path: '/user/listForSelect')]
    public function listForSelect(QData $data)
    {
        $name = (strlen($data->q) > 1) ? $data->q : 'none';

        return ['results' => Criteria::byFilter('user', ['name', 'startswith', $name])
            ->selectRaw("idUser,concat(name,' [',email,']') as name")
            ->orderby('name')
            ->all()];
    }

    #[Get(path: '/user/profile')]
    public function getProfile()
    {
        $user = AppService::getCurrentUser();

        return view('User.profile', [
            'user' => $user,
        ]);

    }

    #[Get(path: '/user/favorites')]
    public function getFavorites()
    {
        $user = AppService::getCurrentUser();
        $favorites = Criteria::table('ak_favorite as f')
            ->join('view_items as i', 'i.idItem', '=', 'f.idItem')
            ->select('i.idItem', 'i.ptTitle', 'i.frTitle', 'i.docDate as date')
            ->all();

        return view('User.favorite', [
            'favorites' => $favorites,
            'locale' => App::getLocale(),
        ]);

    }
}
