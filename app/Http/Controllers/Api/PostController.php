<?php

namespace App\Http\Controllers\Api;

//import model "post"
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//import Resource "postResource"
use App\Http\Resources\PostResource;

//import facade "validator"
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * index
     * 
     * @return void
     */
    public function index()
    {
        // get all posts
        $posts = Post::latest()->paginate(5);

        // return collection of posts as a resource
        return new PostResource(true, 'list data posts', $posts);
    }

    /**
     * store
     * 
     * @param mixed $request
     * @return void
     */
    public function store(Request $request)
    {

        // defining validation rules
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);
        
        // check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());
        
        // create post
        $post = Post::create([
            'image'   => $image->hashName(),
            'title'   => $request->title,
            'content' => $request->content,
        ]);

        //return response
        return new PostResource(true, 'data post berhasil ditambahkan',$post);
    }
   
    public function show($id)
    {
        //find post by ID
        $post = Post::find($id);

        //return single post as a resorce
        return new PostResource(true, 'Details Date Post!', $post);
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(),[
            'title' =>'required | min:10',
            'content' =>'required | min:10',
        ]);

        //check validator
        if($validator->fails()){
            return Response()->json($validator->errors(), 422);
        }

        //get id
        $post = Post::find($id);

        //check image if not empty
        if($request->hasFile('image')){

            $image = $request->file('image');
            $image->storeAs('public/posts',$image->hashName());

             //delete image
        Storage::delete('public/posts/'.basename($post->image));

            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);

        } else {
            //update post without
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }
          //return response
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    public function destroy($id){

        //find post by ID
        $post = Post::find($id);

        //delete image
        Storage::delete('public/posts/'.basename($post->image));

        //delete post
        $post->delete();

        //return response
        return new PostResource(true, 'Data Post Berhasil Dihapus!', null);
    }
}
