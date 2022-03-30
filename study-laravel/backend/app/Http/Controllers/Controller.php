<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redis;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function hello()
    {
        return response()->json([
            'message' => 'hello world'
        ]);
    }

    public function postUser($id)
    {
        if (User::find($id)) {
            return response()->json([
                'message' => 'user is already created'
            ]);
        }
        $user = User::create([
            'email' => "guest{$id}@gmail.com",
            'name' => 'guest',
            'password' => 'guest'
        ]);
        Redis::set("user:{$id}", $user);
        return response()->json($user->toArray());
    }

    public function getUserCache($id)
    {
        $cachedUser = Redis::get("user:{$id}");
        $res = json_decode($cachedUser);
        return response()->json($res);
    }
}
