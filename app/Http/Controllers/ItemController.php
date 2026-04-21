<?php

namespace App\Http\Controllers;

use App\Data\Item\SearchData;
use App\Data\Item\UpdateMetadataData;
use App\Data\Item\UpdateProductionData;
use App\Data\Item\UpdateTitleData;
use App\Database\Criteria;
use App\Repositories\Item;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Put;

#[Middleware(name: 'admin')]
class ItemController extends Controller
{
    #[Get(path: '/items')]
    public function resource()
    {
        return view('Items.resource');
    }

    #[Get(path: '/items/data')]
    public function data(SearchData $search)
    {
        $items = Criteria::table('view_items')
            ->select('idItem', 'ptTitle', 'docDate', 'public')
            ->where('idItemType', 20)
            ->orderBy('idItem', 'desc')
            ->all();
        $data = array_map(fn ($item) => [
            'id' => $item->idItem,
            'name' => $item->ptTitle,
            'docDate' => $item->docDate,
            'public' => $item->public,
            'state' => 'open',
            'type' => 'item',
        ], $items);

        return $data;
    }

    #[Get(path: '/items/grid/{fragment?}')]
    #[Post(path: '/items/grid/{fragment?}')]
    public function grid(SearchData $search, ?string $fragment = null)
    {
        $view = view('Items.grid', [
            'search' => $search,
        ]);

        return is_null($fragment) ? $view : $view->fragment('search');
    }

    #[Get(path: '/items/{id}/edit')]
    public function edit(string $id)
    {
        return view('Items.edit', [
            'item' => Item::byId($id),
        ]);
    }

    #[Get(path: '/items/{id}/formTitle')]
    public function formTitle(string $id)
    {
        return view('Items.formTitle', [
            'item' => Item::byId($id),
        ]);
    }

    #[Get(path: '/items/{id}/formProduction')]
    public function formProduction(string $id)
    {
        $productions = Criteria::table('view_ak_production')
            ->where('idItem', $id)
            ->orderBy('nameType')
            ->orderBy('nameInstance')
            ->all();

        return view('Items.formProduction', [
            'item' => Item::byId($id),
            'idItem' => $id,
            'productions' => $productions,
        ]);
    }

    #[Get(path: '/items/{id}/formMetadata')]
    public function formMetadata(string $id)
    {
        $metadatas = Criteria::table('view_ak_metadata')
            ->where('idItem', $id)
            ->orderBy('nameType')
            ->orderBy('nameInstance')
            ->all();

        return view('Items.formMetadata', [
            'item' => Item::byId($id),
            'idItem' => $id,
            'metadatas' => $metadatas,
        ]);
    }

    #[Put(path: '/items')]
    public function update(UpdateTitleData $data)
    {
        try {
            debug($data);
            // delete as traduções atuais deste item
            Criteria::table('omeka_multilanguage_translations')
                ->where('record_id', $data->idItem)
                ->delete();
            // cria as novas traduções
            $elements = [41, 49, 50];
            foreach ($elements as $element) {
                Criteria::create('omeka_multilanguage_translations', [
                    'locale_code' => 'pt_BR',
                    'record_type' => 'Item',
                    'record_id' => $data->idItem,
                    'element_id' => $element,
                    'text' => $data->ptTitle,
                    'translation' => $data->ptTitle,
                ]);
            }
            foreach ($elements as $element) {
                Criteria::create('omeka_multilanguage_translations', [
                    'locale_code' => 'fr',
                    'record_type' => 'Item',
                    'record_id' => $data->idItem,
                    'element_id' => $element,
                    'text' => $data->ptTitle,
                    'translation' => $data->frTitle,
                ]);
            }

            return $this->renderNotify('success', 'Item title updated.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Get(path: '/items/{idItem}/production')]
    public function getProduction(int $idItem)
    {
        try {
            $productions = Criteria::table('view_ak_production')
                ->where('idItem', $idItem)
                ->orderBy('nameType')
                ->orderBy('nameInstance')
                ->all();

            return view('Items.productions', [
                'idItem' => $idItem,
                'productions' => $productions,
            ]);
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Put(path: '/items/production')]
    public function updateProduction(UpdateProductionData $data)
    {
        try {
            debug($data);
            Criteria::function('production_create(?,?,?)', [
                $data->idItem,
                $data->type,
                $data->instance,
            ]);
            $this->trigger('reload-gridProduction');

            return $this->renderNotify('success', 'Production updated.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/items/production/{idEntityRelation}')]
    public function deleteProduction(string $idEntityRelation)
    {
        try {
            Criteria::deleteById('ak_entityrelation', 'idEntityRelation', $idEntityRelation);
            $this->trigger('reload-gridProduction');

            return $this->renderNotify('success', 'Production removed.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Get(path: '/items/{idItem}/metadata')]
    public function getMetadata(int $idItem)
    {
        try {
            $metadatas = Criteria::table('view_ak_metadata')
                ->where('idItem', $idItem)
                ->orderBy('nameType')
                ->orderBy('nameInstance')
                ->all();

            return view('Items.metadata', [
                'idItem' => $idItem,
                'metadatas' => $metadatas,
            ]);
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Get(path: '/items/metadata/instance/{nameType}')]
    public function getMetadataInstance(string $nameType)
    {
        try {
            return view('Items.instance', [
                'nameType' => $nameType,
            ]);
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Put(path: '/items/metadata')]
    public function updateMetadata(UpdateMetadataData $data)
    {
        try {
            debug($data);
            Criteria::function('metadata_create(?,?,?)', [
                $data->idItem,
                $data->type,
                $data->instance,
            ]);
            $this->trigger('reload-gridMetadata');

            return $this->renderNotify('success', 'Metadata updated.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/items/metadata/{idEntityRelation}')]
    public function deleteMetadata(string $idEntityRelation)
    {
        try {
            Criteria::deleteById('ak_entityrelation', 'idEntityRelation', $idEntityRelation);
            $this->trigger('reload-gridMetadata');

            return $this->renderNotify('success', 'Metadata removed.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }
}
