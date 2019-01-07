<?php

namespace App\Http\Controllers;

use App\Post;
use App\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostsController extends Controller
{
    /**
     * Constructor.
     */
    public function _construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('posts.index', [
            'posts' => Post::orderBy('id', 'desc')->take('20')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|max:255',
        ]);

        $imageName = time().'.'.$request->image->getClientOriginalExtension();

        $request->image->move(public_path('images'), $imageName);

        $post = new Post();

        $post->user_id = Auth::id();
        $post->image = $imageName;
        $post->description = $request->description;

        $post->save();

        return redirect('posts/'.$post->id);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('posts.index', [
            'posts' => [Post::findOrFail($id)],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('posts.edit', [
            'post' => Post::findOrFail($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        //Only save if currently logged in users id matches orginal posters user id (Might not be needed)
        if ($post->id === Auth::id()) {
            $request->validate([
                'description' => 'required|max:255',
            ]);

            $post->description = $request->description;

            $post->save();
        }

        return redirect('posts/'.$post->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        //Only delete if currently logged in users id matches orginal posters user id (Might not be needed)
        if ($post->id === Auth::id()) {
            $post->delete();
        }

        return redirect('posts/');
    }

    public function like($id)
    {
        $record = Like::where([
            ['user_id', Auth::id()],
            ['post_id', $id],
        ]);

        //If our record doesn't exist we create it
        if (null === $record->first()) {
            $like = new Like();

            $like->user_id = Auth::id();
            $like->post_id = $id;
            $like->save();

        //If it exists we delete it
        } else {
            $record->delete();
        }

        return response()->json(null, 200);
    }
}
