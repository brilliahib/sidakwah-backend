<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function findAll()
    {
        $comments = Comment::with([
            'user:id,name,username,profile_picture',
            'materialContent:id,title,sub_modul_id',
            'materialContent.subModul:id,title,modul_id',
            'materialContent.subModul.modul:id,title',
        ])
            ->latest()
            ->get();

        return $this->success($comments, 'Comments retrieved successfully');
    }

    public function store(CreateCommentRequest $request)
    {
        $validated = $request->validated();

        $comment = Comment::create([
            'material_content_id' => $validated['material_content_id'],
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        return $this->success($comment, 'Comment created successfully', 201);
    }

    public function indexByMaterialContent($materialContentId)
    {
        $comments = Comment::with(['user', 'replies'])
            ->where('material_content_id', $materialContentId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->success($comments, 'Comments retrieved successfully');
    }


    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return $this->error('Comment not found', 404);
        }

        if ($comment->user_id !== $request->user()->id) {
            return $this->error('Unauthorized action.', 403);
        }

        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $comment->content = $request->input('content');
        $comment->save();

        return $this->success($comment, 'Comment updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return $this->error('Comment not found', 404);
        }

        if ($comment->user_id !== $request->user()->id) {
            return $this->error('Unauthorized action.', 403);
        }

        $comment->delete();

        return $this->success(null, 'Comment deleted successfully');
    }
}
