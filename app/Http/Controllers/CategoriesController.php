<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }


    public function store(Request $request)
    {
        $id = Auth::id();
        if ($id === 1) {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:50|unique:categories',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $category = Category::create([
                'nombre' => $request->get('nombre'),
            ]);

            return response()->json(['Categoria creada correctamente'=>$category]);
        } else {
            return response()->json('Su usuario no puede realizar esta accion');
        }
    }

    public function destroy($id)
    {
        $id_user = Auth::id();
        if ($id_user === 1) {
            $category = Category::find($id);
            if ($category) {
                $category->delete();
                return response()->json(['Ha borrado la categoria'=> $category]);
            } else {
                return response()->json('No se ha encontrado la categoría');
            }
        }else{
            return response()->json('No tiene permiso para realizar esta acción');
        }
    }
}
