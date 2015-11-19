<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Topic;
use App\Post;
use App\Http\Requests;
use App\Http\Requests\TopicRequest;
use App\Http\Controllers\Controller;

class TopicController extends Controller
{

    protected $nbrPerPage = 4;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$topics = Topic::paginate($this->nbrPerPage);
        $topics = Topic::with('user')
                        ->orderBy('id', 'desc')
                        ->get();

        return view('forum.index', ['topics' => $topics]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('forum/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TopicRequest $request)
    {
        $topic = new Topic;

        $topic->title = $request->input('title');
        $topic->content = $request->input('content');
        $topic->user_id = $request->user()->id;

        $topic->save();

        return view('forum/index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $topic = Topic::find($id);

        $posts = Post::with('user')
                        ->orderBy('id')
                        ->where('topic_id', $id)
                        ->get();

        return view('forum/show', compact('topic', 'posts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
