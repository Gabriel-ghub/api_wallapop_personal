<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show', 'articlesByCategory', 'show']]);
    }

    // Muestro todos los post - PUBLICO
    /**
     * @OA\GET(
     * path="/posts",
     * description="Muestra todos los posts paginados",
     * operationId="show",
     * tags={"posts"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Muestra todos los posts paginados",
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Todos pueden ver los posts",
     *     )
     * )
     */

    public function index(Request $request)
    {
        $post = Post::all();
        return response()->json($post, 200);
    }

    /**
     * @OA\POST(
     * path="/posts/addpost",
     * description="Permite crear un post nuevo",
     * operationId="createPost",
     * tags={"posts"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Create a new post",
     *    @OA\JsonContent(
     *       required={"title","description","price","category","image",},
     *       @OA\Property(property="title", type="string", format="email", example="Coche en venta"),
     *       @OA\Property(property="description", type="string", format="string", example="Coche usado en buen estado"),
     *       @OA\Property(property="price", type="string", format="decimal(10,2)", example="12,00"),
     *       @OA\Property(property="category", type="string", format="string", example="Debe ser un numero"),
     *       @OA\Property(property="image", type="file", format="file", example="image.jpg/png"),
     *       @OA\Property(property="persistent", type="boolean", example="true"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Post creado correctamente",
     *     )
     * )
     */

    public function store(Request $request)
    {
        $id = Auth::id();
        $this->validate($request, [
            'title' => 'required|string',
            'description' => 'required|string',
            // 'price' => 'required|regex:/^\d{1,12}([\.,]\d{1,2})/',
            'price' => 'required',
            'category.*' => 'required|string',
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg'
        ]);

        if (!$request->get('category')) {
            $mensajeError = "Debe seleccionar al menos una categoria";
            $categories = Category::all();
            return response()->json(['message' => 'Error en categoria']);
        }

        $categoriy_id = $request->get('category');
            $category = Category::where('id', $request->get('category'))->count();
            if ($category == 0) {
                echo '<pre>';
                print_r('No supero la validación');
                echo '</pre>';
                exit();
            }
    

        $imagen = $request->file('image');
        $nombre = time() . '-' . $imagen->getClientOriginalName();
        $ruta =  public_path() . '/img/posts';
        $imagen->move($ruta, $nombre);


        $post = Post::create([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'price' => str_replace(',', '.', $request->get('price')),
            'image' => $nombre,
            'user_id' => $id,
        ]);

        $post_id = $post->id;


    
            $this->associateCategory($post_id, $category);
 
        return response()->json(['message' => 'Post creado con exito', 'data' => $post], 200);
    }

    

    public function postsFromUser()
    {
        $id = Auth::id();
        $posts = User::find($id)->posts()->get();
        if ($posts) {
            return response()->json($posts, 200);
        } else {
            return response()->json(['No tienes ningun post']);
        }
    }


    public function show($id)
    {
        $post = Post::find($id);
        if ($post) {
            return response()->json($post, 200);
        } else {
            return response()->json(['No se encontró el post']);
        }
    }



    public function articlesByCategory(Category $category)
    {
        $articles = $category->posts;
        return response()->json(['message' => 'Articulos de la categoría', 'data' => $articles], 200);
    }

    public function update(Request $request)
    {
        $post = Post::findOrFail($request->id);
        $post->update($request->all());
        return response()->json(['message' => 'Esta es la post modificada', 'data' => $post], 200);
    }


    public function associateCategory($post_id, $category)
    {
        $post = Post::find($post_id);
        $category = Category::find($category);
        if ($post->categories()->save($category)) {
            return true;
        }
        return false;
    }
    public function destroy(Request $request, $id )
    {
        $id_user = Auth::id();
        $post = Post::find($id);
        if ($post) {
            if ($id_user === $post->user_id) {
                $post->delete();
                return response()->json(['message' => 'El post borrado fue', 'data' => $post]);
            } else {
                return 'El post no te pertenece, no puedes borrarlo';
            }
        } else if ($id_user === 1) {
            return response()->json(['message' => 'El post borrado fue', 'data' => $post]);
        } else {
            return response()->json(['message' => 'El post no existe']);
        }
    }

}
