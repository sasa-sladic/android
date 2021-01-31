<?php

namespace App\Http\Controllers\Api;

use App\Comment;
use App\Post;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    public function create(Request $request)
    {
        $user = Auth::user();
        $comment = new Comment;
        $comment->user_id = $user->id;
        $comment->post_id = $request->id;
        $comment->comment = $request->comment;
        $comment->save();
        $comment->user;

        $notif = $this->sendNotification($request->id, $user);

        return response()->json([
            'success' => true,
            'comment' => $comment,
            'notification_response', $notif,
            'message' => 'comment added'
        ]);
    }

    private function sendNotification($postId, $userCommented)
    {
        $userId = Post::select('user_id')->where('id', $postId)->first();
        $firebaseToken = User::whereNotNull('device_token')->where('id', $userId['user_id'])->pluck('device_token')->all();

        $SERVER_API_KEY = 'AAAAnqm9b-g:APA91bFVCjtMt9xTzCAbcuJiRaXwkhzVJPUZRx2YfWUlKjppNpSC_nmHmtvtPP50Dh0Ky-Os20HpDFKnI3HpvYUIzseMUfj4rO_Qpl-GgvYydZ7ymLjoGDpFthXzZF2JQvRjPSEK_yJU';

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => 'Blog App',
                "body" => $userCommented->name . ' ' . $userCommented->lastname . ' has commented your post.',
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        return $response;
    }

    public function update(Request $request)
    {
        $comment = Comment::find($request->id);
        //check if user is editing his own comment
        if ($comment->id != Auth::user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'unauthorize access'
            ]);
        }
        $comment->comment = $request->comment;
        $comment->update();

        return response()->json([
            'success' => true,
            'message' => 'comment edited'
        ]);
    }

    public function delete(Request $request)
    {
        $comment = Comment::find($request->id);
        //check if user is editing his own comment
        if ($comment->user_id != Auth::user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'unauthorize access'
            ]);
        }
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'comment deleted'
        ]);
    }

    public function comments(Request $request)
    {
        $comments = Comment::where('post_id', $request->id)->get();
        //show user of each comment
        foreach ($comments as $comment) {
            $comment->user;
        }

        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }
}
