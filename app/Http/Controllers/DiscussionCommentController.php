<?php

namespace App\Http\Controllers;

use App\Models\DiscussionComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiscussionCommentController extends Controller
{
    public function getByDiscussionId($discussionId)
    {
        $comments = DiscussionComment::with('user')
            ->where('discussion_id', $discussionId)
            ->where('is_private', 0)
            ->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Comments for the discussion retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $comments,
        ]);
    }

    public function getMyDiscussionComments()
    {
        $user = Auth::user();

        $comments = DiscussionComment::with('user', 'discussion')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Your discussion comments retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $comments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'discussion_id' => 'required|uuid|exists:discussions,id',
            'comment' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'medical_id' => 'nullable|uuid|exists:users,id',
            'is_private' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('discussion_comments', 'public');
        }

        $comment = DiscussionComment::create([
            'id' => Str::uuid(),
            'discussion_id' => $request->discussion_id,
            'user_id' => auth()->id(),
            'medical_id' => $request->medical_id,
            'is_private' => $request->is_private ?? false,
            'comment' => $request->comment,
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Comment added successfully',
                'statusCode' => 201,
            ],
            'data' => $comment->load('user'),
        ], 201);
    }

    public function destroy($id)
    {
        $comment = DiscussionComment::find($id);

        if (!$comment) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Comment not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        // if ($comment->user_id !== auth()->id()) {
        //     return response()->json([
        //         'meta' => [
        //             'status' => 'error',
        //             'message' => 'Unauthorized to delete this comment',
        //             'statusCode' => 403,
        //         ],
        //         'data' => null,
        //     ], 403);
        // }

        if ($comment->image_path && Storage::disk('public')->exists($comment->image_path)) {
            Storage::disk('public')->delete($comment->image_path);
        }

        $comment->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Comment deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }

    public function show($id)
    {
        $comment = DiscussionComment::with('user')->find($id);

        if (!$comment) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Comment not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Comment detail retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $comment,
        ]);
    }
}
