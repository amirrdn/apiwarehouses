<?php

namespace App\Services;
use App\Models\CategoryModel;
use App\Services\StoreService;
/**
 * Class CategoriesService.
 */
class CategoriesService
{
    private StoreService $st;
    public function __construct(StoreService $st)
    {
        $this->st                   = $st;
    }
    public function categoryRelation($leftjoin = false, $innerjoin = false)
    {
        $category                   = CategoryModel::from('db_category as ct');
        if($leftjoin){
            $category               = $category->leftjoin('db_store as st', 'ct.store_id', 'st.id');
        }else if($innerjoin){
            $category               = $category->join('db_store as st', 'ct.store_id', 'st.id');
        }

        return $category;
    }
    public function store(object $data)
    {
        $initcategory               = $this->st->getStoreByid(\Auth::user()->store_id)->category_init;
        
        $categories                 = new CategoryModel;

        $categories->store_id       = \Auth::user()->store_id;
        $categories->count_id       = CategoryModel::count() + 1;
        $categories->category_code  = $initcategory.$this->code();
        $categories->category_name  = $data->category_name;
        $categories->description    = $data->description;
        $categories->company_id     = $data->company_id;
        $categories->status         = 1;

        $categories->save();
        return $categories;
    }
    public function update(object $data)
    {        
        $categories                 = CategoryModel::find($data->category_id);

        $categories->store_id       = $categories->store_id;
        $categories->count_id       = $categories->count_id;
        $categories->category_code  = $categories->category_code;
        $categories->category_name  = $data->category_name;
        $categories->description    = $data->description;
        $categories->company_id     = $data->company_id;
        $categories->status         = !empty($data->status) ? $data->status : 1;

        $categories->save();
        return $categories;
    }
    public function delete(array $category_id)
    {
        $dbcat                      = CategoryModel::whereIn('id', $category_id);
        if(count($dbcat->get()) > 0){
            $dbcat->delete();

            return response()->json([
                'message'   => 'success delete',
                'code'  => 200
            ]);
        }else{
            return response()->json([
                'message'   => 'error delete',
                'code'  => 400
            ]);
        }
    }
    public function code()
    {
        $code                   = CategoryModel::count();
        $code++;
        return str_pad($code, 4, '0', STR_PAD_LEFT);
    }
}
