<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ItemsService;
use App\Services\FilesService;
Use Exception;

class ItemsController extends Controller
{
    public function ItemsList(Request $request, ItemsService $itm)
    {
        try{
            $items                      = $itm->itemsRelation();
            if(!empty($request->item_type)){
                if($request->item_type == 'items'){
                    $items              = $items->where('itm.service_bit', 0)
                                        ->orWhere('itm.service_bit', null);
                }else if($request->item_type == 'Services'){
                    $items              = $items->where('itm.service_bit', 1);
                }
            }
            if(!empty($request->search)){
                $items                  = $items->where('itm.item_name','LIKE', '%'.request()->search.'%');
            }
            if(!empty($request->array_filter)){
                $items                  = $items->whereIn('itm.service_bit', array($request->array_filter));
            }
            if(!empty($request->order) && !empty($request->sort)){
                $items                  = $items->groupBy('itm.id')
                                        ->orderBy($request->order, $request->sort);
            }else{
                $items                  = $items->groupBy('itm.id')
                                        ->orderBy('itm.item_code', 'desc');
            }
            $items                      = $items ->select('itm.*', 'c.category_name', 
                                        'br.brand_name', 'u.unit_name', 'tx.tax', 
                                        'tx.tax_name',
                                        \DB::raw("(SELECT available_qty FROM db_warehouseitems WHERE item_id = itm.id group by item_id) as available_qty"));
            if(!empty($request->per_page)){
                $items                  = $items->paginate(request()->per_page);
            }else{
                $items                  = $items->get();
            }
    
            return response()->json([
                'message'   => 'success',
                'code'  => 200,
                'data'  => $items
            ]);
        } catch(Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    public function store(Request $request, ItemsService $itm, FilesService $uploads)
    {
        $filesdb = '';
        $validator = \Validator::make($request->all(), [
            'item_name' => 'required',
            'category_id' => 'required',
            'unit_id' => 'required',
            'tax_id' => 'required',
        ]);
        // if($request->file('files')){
        //     $validator = \Validator::make($request->all(), [
        //         'item_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        //     ]);
        // }
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message' => 'error insert items',
                'code' => 400,
                'error' => $error
            ]);
        }

        $ffiles = $request->get('files_image');
        if (stripos($ffiles, "data:image/") !== false) {
                 request()->merge([
                'name'  => $request->item_name
            ]);
            $filesdb    = $uploads->UploadFiles($request);
        }
        request()->merge([
            'item_image' => $filesdb
        ]);

        $data = $request->all();
        $item = $itm->store($request);
        return response()->json([
            'message'   => 'success',
            'code'  => 200
        ]);
    }
    public function view($id, ItemsService $itm)
    {
        $items                      = $itm->itemsRelation()
                                    ->where('itm.id', $id)
                                    ->select('itm.*', 'aj.id as adjust_id', 'aj.adjustment_id', 'us.first_name', 'us.last_name')
                                    ->first();
        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $items
        ]);
    }
    public function update(Request $request, ItemsService $itm, FilesService $uploads)
    {
        $filesdb;
        $validator = \Validator::make($request->all(), [
            'item_name' => 'required',
            'category_id' => 'required',
            'unit_id' => 'required',
            'tax_id' => 'required',
        ]);
        // if($request->file('files_image')){
        //     $validator = \Validator::make($request->all(), [
        //         'item_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        //     ]);
        // }
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message' => 'error insert items',
                'code' => 400,
                'error' => $error
            ]);
        }
        $ffiles = $request['files_image'];
        if (stripos($ffiles, "data:image/") !== false) {
            if($request->has('files_image')){
                request()->merge([
                    'name'  => $request->item_name
                ]);
                $filesdb    = $uploads->UploadFiles($request);
            }
        }else{
            $filesdb        = $request->item_image;
        }
        request()->merge([
            'item_image' => $filesdb
        ]);
        return $itm->update($request);
    }
    public function destroy(Request $request, ItemsService $itm)
    {
        return $itm->delete($request);
    }
}
