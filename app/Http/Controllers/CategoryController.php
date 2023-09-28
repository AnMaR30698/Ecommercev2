<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::all();
        $categoriesWithImages = [];

        foreach ($categories as $category) {
            $url = Storage::url($category->image);
            $categoriesWithImages[] = [
                'id' => $category->id,
                'name' => $category->name,
                'image_url' => $url,
            ];
        }
        return response()->json([
            'status' => 'success',
            'data' => $categoriesWithImages,
        ]);
    }
    public function store(Request $request)
    {
        $user= Auth::user();
        if($user->hasAnyRole(['admin'])){
            $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'required',
            ]);
        $isExist = Category::where('name',$request->name)->first();
        if($isExist){
            return response()->json([
                'status' => 'error',
                'message' => 'Category Already Exist',
                'data' =>$isExist
                ]);
        }
        $category = new Category;
        $category->name = $request->input('name');
        $path = $request->file('image')->store('public/images');
        $url = Storage::url($path);
        $category->image = $path;       
        $category->save();

        return response()->json([
            'status'=>'success',
            'message' => 'Category created successfully',
            'category_name' => $category->name,
            'category_image' => $url,
        ], Response::HTTP_CREATED);
    }
    else{
        return response()->json([
            'status'=>'error',
            'message' => 'you can not access here',
        ],Response::HTTP_FORBIDDEN);
    }
    }
    public function update(Request $request, $id)
    {
        $user= Auth::user();
        if($user->hasAnyRole(['admin'])){
            $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'required',
            ]);
            $category = Category::find($id);
        if(!$category){
            return response()->json([
                'status'=>'error',
                'message' => 'Category not found',
                
            ]);
        }
        $isExist = Category::whereNotIn('id',[$id])
                           ->where('name',$request->name)
                           ->first();
        if($isExist){
            return response()->json([
                'status' => 'error',
                'message' => 'Category with this name Already Exist',
                'data' =>$isExist
                ]);
        }

        $category->name = $request->input('name');
        $path = $request->file('image')->store('public/images');
        $url = Storage::url($path);
        $category->image = $path; 
        $category->save();

        return response()->json([
            'message' => 'Category updated successfully',
            'category_name' => $category->name,
            'category_image' => $url,
        ],Response::HTTP_ACCEPTED);
        }else{
            return response()->json([
                'status'=>'error',
                'message' => 'you can not access here',
            ],REsponse::HTTP_FORBIDDEN);
        }
    }
    public function destroy($id)
    {
        $user= Auth::user();
        if($user->hasAnyRole(['admin'])){

        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'status'=>'error',
                'message' => 'Category not found',
            ],Response::HTTP_NOT_FOUND);
        }
        $category->delete();

        return response()->json([
            'status'=>'success',
            'message' => 'Category deleted successfully',
        ],Response::HTTP_ACCEPTED);
     }else{
            return response()->json([
                'status'=>'error',
                'message' => 'you can not access here',
            ],Response::HTTP_FORBIDDEN);
        }

    }
}
