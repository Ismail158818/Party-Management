<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Models\Event;
use App\Http\Resources\CommentResource;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;  // إضافة الـ Trait
use Illuminate\Http\Request;

class CommentApiController extends Controller
{
    use ApiResponse;  // استخدام الـ Trait

    public function index(Request $request)
    {
        // التحقق من المدخلات
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id', // تحقق من وجود الحدث
        ]);
        
        // استرجاع التعليقات المرتبطة بالحدث
        $comments = Comment::where('event_id', $validated['event_id'])->get();
        
        if ($comments->isEmpty()) {
            return $this->error('لا توجد تعليقات لهذا الحدث', 404);  // استخدام دالة الخطأ من الـ Trait
        } else {
            return $this->success(CommentResource::collection($comments));  // استخدام دالة النجاح من الـ Trait
        }
    }

    public function store(Request $request)
    {
        // التحقق من المدخلات
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id', // تحقق من وجود الحدث
            'text' => 'required|string|max:255',
        ]);
        $isJoined = Event::where('id', $event_id)
        ->whereHas('users', fn($q) => $q->where('user_id', auth()->id()))
        ->exists();
    
        if (!$isJoined) {
            return $this->error('يجب أن تكون منضمًا للحدث لتتمكن من إضافة تعليق', 403);  // استخدام دالة الخطأ من الـ Trait
        }

        // إضافة تعليق جديد
        $comment = Comment::create([
            'user_id' => auth()->user()->id, // المستخدم الحالي
            'event_id' => $validated['event_id'], // الحدث المعين
            'text' => $validated['text'],
        ]);

        return $this->success(new CommentResource($comment), 'تم إضافة التعليق بنجاح', 201);  // استخدام دالة النجاح من الـ Trait
    }

    public function destroy(Request $request)   
    {
        $comment = Comment::find($request->comment_id);

        if (!$comment) {
            return $this->error('التعليق غير موجود', 404);  // استخدام دالة الخطأ من الـ Trait
        }

        if(auth()->id() == $comment->user_id || auth()->user()->is_admin) {
            $comment->delete();
            return $this->success(null, 'تم حذف التعليق بنجاح', 200);  // استخدام دالة النجاح من الـ Trait
        } else {
            return $this->error('ليس لديك صلاحية لحذف هذا التعليق', 403);  // استخدام دالة الخطأ من الـ Trait
        }
    }
    

    public function update(Request $request)   
    {
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'comment_id' => 'required|integer',
            'text' => 'required|string|max:255',
        ]);

        // البحث عن التعليق
        $comment = Comment::find($request->comment_id);
        if (!$comment) {
            return $this->error('التعليق غير موجود', 404);  // استخدام دالة الخطأ من الـ Trait
        }

        // التحقق من صلاحية المستخدم لتعديل التعليق
        if (auth()->user()->id != $comment->user_id) {
            return $this->error('ليس لديك صلاحية لتعديل هذا التعليق', 403);  // استخدام دالة الخطأ من الـ Trait
        }

        // تعديل نص التعليق
        $comment->text = $request->text;
        $comment->save();

        return $this->success(new CommentResource($comment), 'تم تعديل التعليق بنجاح', 200);  // استخدام دالة النجاح من الـ Trait
    }
}
