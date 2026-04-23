<?php 

<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
=======
>>>>>>> Stashed changes
$router->get('timeline', [
	'uses' => 'App\Http\Controllers\TimelineController@timeline',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('timeline/update', [
	'uses' => 'App\Http\Controllers\TimelineController@timelineUpdate',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('item-pt', [
	'uses' => 'App\Http\Controllers\DocumentController@itemPt',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('item-fr', [
	'uses' => 'App\Http\Controllers\DocumentController@itemFr',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('favorite', [
	'uses' => 'App\Http\Controllers\DocumentController@favorite',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('item/{idItem}/translate/{lang}', [
	'uses' => 'App\Http\Controllers\DocumentController@translate',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('item/{idItem}/citation', [
	'uses' => 'App\Http\Controllers\DocumentController@citation',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user', [
	'uses' => 'App\Http\Controllers\UserController@resource',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/new', [
	'uses' => 'App\Http\Controllers\UserController@new',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\UserController@grid',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('user/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\UserController@grid',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/{id}/edit', [
	'uses' => 'App\Http\Controllers\UserController@edit',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\UserController@formEdit',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('user/{id}/authorize', [
	'uses' => 'App\Http\Controllers\UserController@authorizeUser',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('user', [
	'uses' => 'App\Http\Controllers\UserController@update',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('user/new', [
	'uses' => 'App\Http\Controllers\UserController@create',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('user/{id}', [
	'uses' => 'App\Http\Controllers\UserController@delete',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/listForSelect', [
	'uses' => 'App\Http\Controllers\UserController@listForSelect',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/profile', [
	'uses' => 'App\Http\Controllers\UserController@getProfile',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/favorites', [
	'uses' => 'App\Http\Controllers\UserController@getFavorites',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
$router->get('/', [
	'uses' => 'App\Http\Controllers\AppController@main',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('apresentacao', [
	'uses' => 'App\Http\Controllers\AppController@presentation',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acervos', [
	'uses' => 'App\Http\Controllers\AppController@collection',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('politicaeditorial', [
	'uses' => 'App\Http\Controllers\AppController@editorial',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('equipe', [
	'uses' => 'App\Http\Controllers\AppController@team',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('condicoesdeuso', [
	'uses' => 'App\Http\Controllers\AppController@terms',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('bibliografia', [
	'uses' => 'App\Http\Controllers\AppController@bibliography',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('contato', [
	'uses' => 'App\Http\Controllers\AppController@contato',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('contato', [
	'uses' => 'App\Http\Controllers\AppController@contatoPost',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('changeLanguage/{language}', [
	'uses' => 'App\Http\Controllers\AppController@changeLanguage',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
=======
>>>>>>> Stashed changes
$router->get('components/fesByFrame', [
	'uses' => 'App\Http\Controllers\ComponentsController@feCombobox',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/recente', [
	'uses' => 'App\Http\Controllers\AccessController@accessRecent',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/ano', [
	'uses' => 'App\Http\Controllers\AccessController@accessYear',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/categoria', [
	'uses' => 'App\Http\Controllers\AccessController@accessCategory',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/acervo', [
	'uses' => 'App\Http\Controllers\AccessController@accessCollection',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/id', [
	'uses' => 'App\Http\Controllers\AccessController@accessId',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('imagens', [
	'uses' => 'App\Http\Controllers\ImagesController@accessRecent',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('pesquisar', [
	'uses' => 'App\Http\Controllers\SearchController@search',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('pesquisar/metadata/instancias', [
	'uses' => 'App\Http\Controllers\SearchController@metadataInstances',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('pesquisar', [
	'uses' => 'App\Http\Controllers\SearchController@searchBy',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('auth0Callback', [
	'uses' => 'App\Http\Controllers\LoginController@auth0Callback',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('auth0Login', [
	'uses' => 'App\Http\Controllers\LoginController@auth0Login',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('login', [
	'uses' => 'App\Http\Controllers\LoginController@login',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('login-error', [
	'uses' => 'App\Http\Controllers\LoginController@loginError',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('logout', [
	'uses' => 'App\Http\Controllers\LoginController@logout',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
$router->get('sandbox/page1', [
	'uses' => 'App\Http\Controllers\SandboxController@page1',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('sandbox/page2', [
	'uses' => 'App\Http\Controllers\SandboxController@page2',
	'as' => NULL,
	'middleware' => ['auth'],
	'where' => [],
	'domain' => NULL,
]);

<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
=======
>>>>>>> Stashed changes
$router->get('biografias', [
	'uses' => 'App\Http\Controllers\BiographyController@biografias',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('biografias/item/{idItem}', [
	'uses' => 'App\Http\Controllers\BiographyController@itemBiografia',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('biografias/item/{idItem}/fragment', [
	'uses' => 'App\Http\Controllers\BiographyController@fragmentBiografia',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('biografias/item/{idItem}/citation', [
	'uses' => 'App\Http\Controllers\BiographyController@citation',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
$router->get('items', [
	'uses' => 'App\Http\Controllers\ItemController@resource',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('items/data', [
	'uses' => 'App\Http\Controllers\ItemController@data',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('items/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\ItemController@grid',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('items/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\ItemController@grid',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('items/{id}/edit', [
	'uses' => 'App\Http\Controllers\ItemController@edit',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('items/{id}/formTitle', [
	'uses' => 'App\Http\Controllers\ItemController@formTitle',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('items/{id}/formProduction', [
	'uses' => 'App\Http\Controllers\ItemController@formProduction',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('items/{id}/formMetadata', [
	'uses' => 'App\Http\Controllers\ItemController@formMetadata',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('items', [
	'uses' => 'App\Http\Controllers\ItemController@update',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('items/{idItem}/production', [
	'uses' => 'App\Http\Controllers\ItemController@getProduction',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('items/production', [
	'uses' => 'App\Http\Controllers\ItemController@updateProduction',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('items/production/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\ItemController@deleteProduction',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('items/{idItem}/metadata', [
	'uses' => 'App\Http\Controllers\ItemController@getMetadata',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('items/metadata/instance/{nameType}', [
	'uses' => 'App\Http\Controllers\ItemController@getMetadataInstance',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('items/metadata', [
	'uses' => 'App\Http\Controllers\ItemController@updateMetadata',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('items/metadata/{idEntityRelation}', [
	'uses' => 'App\Http\Controllers\ItemController@deleteMetadata',
	'as' => NULL,
	'middleware' => ['admin'],
	'where' => [],
	'domain' => NULL,
]);

<<<<<<< Updated upstream
<<<<<<< Updated upstream
$router->get('timeline', [
	'uses' => 'App\Http\Controllers\TimelineController@timeline',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('timeline/update', [
	'uses' => 'App\Http\Controllers\TimelineController@timelineUpdate',
	'as' => NULL,
	'middleware' => ['web'],
=======
=======
>>>>>>> Stashed changes
$router->get('empty', [
	'uses' => 'App\Http\Controllers\Controller@empty',
	'as' => NULL,
	'middleware' => [],
<<<<<<< Updated upstream
>>>>>>> Stashed changes
	'where' => [],
	'domain' => NULL,
]);

$router->get('auth0Callback', [
	'uses' => 'App\Http\Controllers\LoginController@auth0Callback',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('auth0Login', [
	'uses' => 'App\Http\Controllers\LoginController@auth0Login',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('login', [
	'uses' => 'App\Http\Controllers\LoginController@login',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('login-error', [
	'uses' => 'App\Http\Controllers\LoginController@loginError',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('logout', [
	'uses' => 'App\Http\Controllers\LoginController@logout',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('empty', [
	'uses' => 'App\Http\Controllers\Controller@empty',
	'as' => NULL,
	'middleware' => [],
	'where' => [],
	'domain' => NULL,
]);

$router->get('pesquisar', [
	'uses' => 'App\Http\Controllers\SearchController@search',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('pesquisar/metadata/instancias', [
	'uses' => 'App\Http\Controllers\SearchController@metadataInstances',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('pesquisar', [
	'uses' => 'App\Http\Controllers\SearchController@searchBy',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/recente', [
	'uses' => 'App\Http\Controllers\AccessController@accessRecent',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/ano', [
	'uses' => 'App\Http\Controllers\AccessController@accessYear',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/categoria', [
	'uses' => 'App\Http\Controllers\AccessController@accessCategory',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/acervo', [
	'uses' => 'App\Http\Controllers\AccessController@accessCollection',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('acesso/id', [
	'uses' => 'App\Http\Controllers\AccessController@accessId',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user', [
	'uses' => 'App\Http\Controllers\UserController@resource',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/new', [
	'uses' => 'App\Http\Controllers\UserController@new',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\UserController@grid',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('user/grid/{fragment?}', [
	'uses' => 'App\Http\Controllers\UserController@grid',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/{id}/edit', [
	'uses' => 'App\Http\Controllers\UserController@edit',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/{id}/formEdit', [
	'uses' => 'App\Http\Controllers\UserController@formEdit',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->put('user/{id}/authorize', [
	'uses' => 'App\Http\Controllers\UserController@authorizeUser',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('user', [
	'uses' => 'App\Http\Controllers\UserController@update',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('user/new', [
	'uses' => 'App\Http\Controllers\UserController@create',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->delete('user/{id}', [
	'uses' => 'App\Http\Controllers\UserController@delete',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/listForSelect', [
	'uses' => 'App\Http\Controllers\UserController@listForSelect',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/profile', [
	'uses' => 'App\Http\Controllers\UserController@getProfile',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('user/favorites', [
	'uses' => 'App\Http\Controllers\UserController@getFavorites',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('biografias', [
	'uses' => 'App\Http\Controllers\BiographyController@biografias',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('biografias/item/{idItem}', [
	'uses' => 'App\Http\Controllers\BiographyController@itemBiografia',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('biografias/item/{idItem}/fragment', [
	'uses' => 'App\Http\Controllers\BiographyController@fragmentBiografia',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('biografias/item/{idItem}/citation', [
	'uses' => 'App\Http\Controllers\BiographyController@citation',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('imagens', [
	'uses' => 'App\Http\Controllers\ImagesController@accessRecent',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('item-pt', [
	'uses' => 'App\Http\Controllers\DocumentController@itemPt',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('item-fr', [
	'uses' => 'App\Http\Controllers\DocumentController@itemFr',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->post('favorite', [
	'uses' => 'App\Http\Controllers\DocumentController@favorite',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('item/{idItem}/translate/{lang}', [
	'uses' => 'App\Http\Controllers\DocumentController@translate',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('item/{idItem}/citation', [
	'uses' => 'App\Http\Controllers\DocumentController@citation',
	'as' => NULL,
	'middleware' => ['web'],
	'where' => [],
	'domain' => NULL,
]);

$router->get('components/fesByFrame', [
	'uses' => 'App\Http\Controllers\ComponentsController@feCombobox',
	'as' => NULL,
	'middleware' => ['web'],
=======
>>>>>>> Stashed changes
	'where' => [],
	'domain' => NULL,
]);
