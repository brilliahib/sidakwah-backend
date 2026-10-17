<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function findAll(Request $request)
    {
        $comments = Comment::with([
            'user:id,name,username,profile_picture',
            'materialContent:id,title,sub_modul_id',
            'materialContent.subModul:id,title,modul_id',
            'materialContent.subModul.modul:id,title',
        ])
            ->visibleTo($request->user())
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
            'content' => trim($validated['content']),
            'status' => Comment::STATUS_PENDING,
        ]);

        return $this->success($comment->load('user:id,name,username,profile_picture'), 'Comment submitted for review', 201);
    }

    public function indexByMaterialContent($materialContentId)
    {
        $replyVisibility = null;
        $replyVisibility = function ($query) use (&$replyVisibility) {
            $query->where('status', Comment::STATUS_APPROVED)
                ->with([
                    'user:id,name,username,profile_picture',
                    'replies' => $replyVisibility,
                ]);
        };

        $comments = Comment::with([
            'user:id,name,username,profile_picture',
            'replies' => $replyVisibility,
        ])
            ->where('status', Comment::STATUS_APPROVED)
            ->where('material_content_id', $materialContentId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->success($comments, 'Comments retrieved successfully');
    }

    public function myMaterialStatus(Request $request, $materialContentId)
    {
        $comments = Comment::query()
            ->with('user:id,name,username,profile_picture')
            ->where('material_content_id', $materialContentId)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', [Comment::STATUS_PENDING, Comment::STATUS_REJECTED])
            ->latest()
            ->get();

        return $this->success($comments, 'Your discussion status retrieved successfully');
    }


    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return $this->error('Comment not found', 404);
        }

        if ($comment->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return $this->error('Unauthorized action.', 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        if (trim($validated['content']) === '') {
            return $this->error('Komentar tidak boleh kosong.', 422);
        }

        $comment->content = trim($validated['content']);
        $comment->status = Comment::STATUS_PENDING;
        $comment->moderated_by = null;
        $comment->moderated_at = null;
        $comment->save();

        return $this->success($comment, 'Comment resubmitted for review');
    }

    public function moderate(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $comment = Comment::find($id);
        if (!$comment) {
            return $this->error('Comment not found', 404);
        }

        $updated = Comment::query()
            ->whereKey($id)
            ->where('status', Comment::STATUS_PENDING)
            ->update([
                'status' => $validated['status'],
                'moderated_by' => $request->user()->id,
                'moderated_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            return $this->error('This discussion has already been moderated.', 409);
        }

        return $this->success(
            Comment::with(['user:id,name,username,profile_picture', 'moderator:id,name'])
                ->findOrFail($id),
            'Comment moderation saved successfully'
        );
    }

    public function destroy(Request $request, $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return $this->error('Comment not found', 404);
        }

        if ($comment->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return $this->error('Unauthorized action.', 403);
        }

        $comment->delete();

        return $this->success(null, 'Comment deleted successfully');
    }
}
