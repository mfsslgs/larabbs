<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TopicRequest;
use App\Models\Category;
use App\Handlers\ImageUploadHandler;
use Auth;
use App\Models\User;
use App\Models\Link;

class TopicsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    public function index(Request $request, Topic $topic, User $user, Link $link)
      {
          $topics = $topic->withOrder($request->order)->paginate(20);
          $active_users = $user->getActiveUsers();
          $links = $link->getAllCached();

          return view('topics.index', compact('topics', 'active_users', 'links'));
      }

    public function show(Topic $topic,Request $request)
    {
        if(!empty($topic->slug) && $topic->slug != $request->slug){
          return redirect($topic->link(),301);
        }
        return view('topics.show', compact('topic'));
    }

	public function create(Topic $topic)
	{
    $categories = Category::all();
		return view('topics.create_and_edit', compact('topic','categories'));
	}

	public function store(TopicRequest $request,Topic $topic)
	{
    $topic->fill($request->all());
    $topic->user_id = Auth::id();
		$topic->save();
		return redirect()->to($topic->link())->with('message', '成功创建话题！');
	}

	public function edit(Topic $topic)
	{
        $this->authorize('update', $topic);
        $categories = Category::all();
		    return view('topics.create_and_edit', compact('topic','categories'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
		$this->authorize('update', $topic);
		$topic->update($request->all());

		return redirect()->to($topic->link())->with('message', '更新成功！');
	}

	public function destroy(Topic $topic)
	{
		$this->authorize('destroy', $topic);
		$topic->delete();

		return redirect()->route('topics.index')->with('message', '成功删除！');
	}

  public function uploadImage(Request $request,ImageUploadHandler $uploader){

    $data = [
      'success'   => false,
      'msg'       => '上传失败！',
      'file_path' => '',
    ];

    if($file = $request->upload_file){
      $result = $uploader->save($request->upload_file,'topics',\Auth::id(),1024);
      if($result){
        $data['file_path'] = $result['path'];
        $data['msg'] = '上传成功！';
        $data['success'] = true;
      }
    }

    return $data;

  }
}
