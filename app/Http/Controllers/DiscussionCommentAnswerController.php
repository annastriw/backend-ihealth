<?php

namespace App\Http\Controllers;

use App\Models\DiscussionCommentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiscussionCommentAnswerController extends Controller
{
    public function getByCommentId($commentId)
    {
        $answers = DiscussionCommentAnswer::with('user')->where('discussion_comment_id', $commentId)->get();

        $formatted = $answers->map(function ($answer) {
            return [
                'id' => $answer->id,
                'comment' => $answer->comment,
                'image_path' => $answer->image_path,
                'created_at' => $answer->created_at,
                'updated_at' => $answer->updated_at,
                'user' => [
                    'id' => $answer->user->id,
                    'name' => $answer->user->name,
                    'email' => $answer->user->email,
                    'username' => $answer->user->username,
                ],
            ];
        });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Answers for the comment retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $formatted,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'discussion_comment_id' => 'required|uuid|exists:discussion_comments,id',
            'comment' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('discussion_comment_answers', 'public');
        }

        $answer = DiscussionCommentAnswer::create([
            'id' => Str::uuid(),
            'discussion_comment_id' => $request->discussion_comment_id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Answer added successfully',
                'statusCode' => 201,
            ],
            'data' => $answer->load('user'),
        ], 201);
    }

    public function destroy($id)
    {
        $answer = DiscussionCommentAnswer::find($id);

        if (!$answer) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Answer not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        if ($answer->user_id !== auth()->id()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this answer',
                    'statusCode' => 403,
                ],
                'data' => null,
            ], 403);
        }

        if ($answer->image_path && Storage::disk('public')->exists($answer->image_path)) {
            Storage::disk('public')->delete($answer->image_path);
        }

        $answer->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Answer deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }
}
