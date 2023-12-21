<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoriesService;
Use Exception;

class CategoriesController extends Controller
{
    public function getCategories(Request $request, CategoriesService $ct)
    {
        try{
            $category                   = $ct->categoryRelation();
            if(!empty($request->search_category)){
                $search_category        = $request->search_category;
                $category               = $category->where(function($query) use ($search_category){
                                            $query->whereRaw('upper(category_name) LIKE "%'.strtoupper($search_category).'%"')
                                            ->orWhereRaw('upper(category_code) LIKE "%'.strtoupper($search_category).'%"');
                                        });
            }
            if(!empty($request->order) && !empty($request->sort)){
                $category               = $category->orderBy($request->order, $request->sort);
            }else{
                $category               = $category->orderBy('category_name', 'asc');
            }
    
            if(!empty($request->per_page)){
                $category               = $category->paginate(request()->per_page);
            }else{
                $category               = $category->get();
            }
    
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $category
            ]);
        } catch(Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    public function store(Request $request, CategoriesService $ct)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'category_name' => 'required'
            ],[
                'category_name.required' => 'Category name is required !'
            ]);
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            $insert = $ct->store($request);

            return response()->json([
                'message'   => 'success insert category',
                'code'  => 200,
                'data'  => $insert
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function view(Request $request, CategoriesService $ct)
    {
        try{
            if(\Auth::user()->role_id == 1 || \Auth::user()->role_id == 2 || \Auth::user()->role_name.includes('admin') ){
                $categories             = $ct->categoryRelation(true, false);
            }else{
                $categories             = $ct->categoryRelation(false, true);
            }
            $categories                 = $categories->where('ct.id', $request->category_id)
                                        ->select('ct.*', 'st.store_name')
                                        ->first();
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $categories
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function update(Request $request, CategoriesService $ct)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'category_name' => 'required',
                'category_id' => 'required',
            ],[
                'category_name.required' => 'Category name is required !',
                'category_id.required' => 'ID is required !',
            ]);
            if ($validator->fails()) {
                $error = $validator->errors();
                return response()->json([
                    'message'   => $error,
                    'code'  => 400
                ]);
            }
            $insert = $ct->update($request);

            return response()->json([
                'message'   => 'success insert category',
                'code'  => 200,
                'data'  => $insert
            ]);
        } catch(Exception $e) {
            return response()->json([
                'message'   => $e->getMessage()
            ], 400);
        }
    }
    public function destroy(Request $request, CategoriesService $ct)
    {
        $validator = \Validator::make($request->all(), [
            'category_id' => 'required'
        ],[
            'id.required' => 'Category ID is required !'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        $delete = $ct->delete($request->category_id);
        return response()->json([
            'message'   => 'success delete data',
            'code'  => 200
        ]);
    }
}
