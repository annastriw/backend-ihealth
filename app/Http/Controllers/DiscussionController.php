<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscussionController extends Controller
{
    public function index()
    {
        $discussions = Discussion::with('comments.user')->orderBy('created_at', 'desc')->get();

        $formatted = $discussions->map(function ($discussion) {
            return [
                'id' => $discussion->id,
                'title' => $discussion->title,
                'created_at' => $discussion->created_at,
                'updated_at' => $discussion->updated_at,
                'comments' => $discussion->comments->sortByDesc('created_at')->values()->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'image_path' => $comment->image_path,
                        'created_at' => $comment->created_at,
                        'updated_at' => $comment->updated_at,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                            'username' => $comment->user->username,
                            'email' => $comment->user->email,
                        ]
                    ];
                }),
            ];
        });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'List of Discussions retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $formatted,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $discussion = Discussion::create([
            'id' => Str::uuid(),
            'title' => $request->title,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Discussion created successfully',
                'statusCode' => 201,
            ],
            'data' => $discussion,
        ], 201);
    }

    public function showForAdmin($id)
    {
        $discussion = Discussion::with(['comments.user', 'comments.answers'])->findOrFail($id);

        $formatted = [
            'id' => $discussion->id,
            'title' => $discussion->title,
            'created_at' => $discussion->created_at,
            'updated_at' => $discussion->updated_at,
            'comments' => $discussion->comments
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'is_private' => $comment->is_private,
                        'image_path' => $comment->image_path,
                        'created_at' => $comment->created_at,
                        'updated_at' => $comment->updated_at,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                        'answers' => $comment->answers->map(function ($answer) {
                            return [
                                'id' => $answer->id,
                                'comment' => $answer->comment,
                                'image_path' => $answer->image_path,
                                'created_at' => $answer->created_at,
                                'user' => [
                                    'id' => $answer->user->id,
                                    'name' => $answer->user->name,
                                ],
                            ];
                        }),
                    ];
                }),
        ];

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Discussion details retrieved for admin successfully',
                'statusCode' => 200,
            ],
            'data' => $formatted,
        ]);
    }

    public function show($id)
    {
        $discussion = Discussion::with(['comments.user', 'comments.answers.user'])->findOrFail($id);

        $formatted = [
            'id' => $discussion->id,
            'title' => $discussion->title,
            'created_at' => $discussion->created_at,
            'updated_at' => $discussion->updated_at,
            'comments' => $discussion->comments
                ->filter(function ($comment) {
                    if ($comment->is_private == 0) {
                        return true;
                    }

                    return $comment->user_id === auth()->id();
                })
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'image_path' => $comment->image_path,
                        'created_at' => $comment->created_at,
                        'updated_at' => $comment->updated_at,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                        'answers' => $comment->answers->map(function ($answer) {
                            return [
                                'id' => $answer->id,
                                'comment' => $answer->comment,
                                'image_path' => $answer->image_path,
                                'created_at' => $answer->created_at,
                                'user' => [
                                    'id' => $answer->user->id,
                                    'name' => $answer->user->name,
                                ],
                            ];
                        }),
                    ];
                }),
        ];

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Discussion details retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $formatted,
        ]);
    }


    public function showPrivateDiscussions()
    {
        $userId = auth()->id();

        $comments = \App\Models\DiscussionComment::with(['user', 'answers'])
            ->where('is_private', 1)
            ->where('medical_id', $userId)
            ->latest()
            ->get();

        $formatted = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'image_path' => $comment->image_path,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'discussion_id' => $comment->discussion_id,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ],
                'answers' => $comment->answers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'comment' => $answer->comment,
                        'image_path' => $answer->image_path,
                        'created_at' => $answer->created_at,
                        'user' => [
                            'id' => $answer->user->id,
                            'name' => $answer->user->name,
                        ],
                    ];
                }),
            ];
        });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Private discussion comments retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $formatted,
        ]);
    }

    public function update(Request $request, $id)
    {
        $discussion = Discussion::find($id);

        if (!$discussion) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Discussion not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $request->validate([
            'title' => 'nullable|string',
        ]);

        $discussion->title = $request->title ?? $discussion->title;
        $discussion->save();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Discussion updated successfully',
                'statusCode' => 200,
            ],
            'data' => $discussion,
        ]);
    }

    public function destroy($id)
    {
        $discussion = Discussion::find($id);

        if (!$discussion) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Discussion not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $discussion->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Discussion deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }
}
