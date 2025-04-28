<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserApiController extends Controller
{
    public function toggleJoin(Request $request)
    {
        // Verify user is not an admin
        if (auth()->user()->is_admin != 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admins are not allowed to perform this action'
            ], 403);
        }

        // Validate request inputs
        $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'quantity_tickets' => 'required|integer'
        ]);

        $event = Event::find($request['event_id']);
        $user = auth()->user();

        // Check if there are enough tickets
        if ($request['quantity_tickets'] > $event->tickets_limit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not enough tickets available'
            ], 400);
        }

        try {
            // Toggle join/unjoin for the user
            $result = $event->users()->toggle($user->id);

            // Adjust tickets limit if joined
            if (!empty($result['attached'])) {
                $event->tickets_limit -= $request['quantity_tickets'];
                $event->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Joined successfully',
                    'remaining_tickets' => $event->tickets_limit
                ], 200);
            } elseif (!empty($result['detached'])) {
                $event->tickets_limit += $request['quantity_tickets'];
                $event->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Unjoined successfully',
                    'remaining_tickets' => $event->tickets_limit
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Action failed'
            ], 500);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while toggling join/unjoin action',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Display the specified resource. */
    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ], 200);
    }

    /** Update the specified resource. */
    public function update(Request $request)
    {
        // التحقق من المدخلات
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:8|confirmed',
            'old_password' => 'nullable|string|min:8',
        ]);
    
        // التحقق من كلمة المرور القديمة
        if (!Hash::check($validated['old_password'], auth()->user()->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Old password is incorrect'
            ], 400); 
        }
    
        // التحقق من أن المستخدم الحالي يحاول تحديث نفسه
        if (auth()->user()->id === $request->id) {
            // العثور على المستخدم
            $user = User::find($request->id);
    
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }
    
            // تحديث البيانات إذا كانت موجودة في الطلب
            if ($request->name) {
                $user->name = $request->name;
            }
            if ($request->email) {
                $user->email = $request->email;
            }
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
    
            // حفظ التحديثات
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully'
            ], 200);
        }
    
        // إذا حاول المستخدم تعديل بيانات مستخدم آخر
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized to update this user'
        ], 403);
    }
    

    /** Remove the specified resource from storage. */
    public function destroy(string $id)
    {
        if (auth()->user()->is_admin === true || auth()->user()->id == $id) {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            $user->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized to delete this user'
        ], 403);
    }
    public function search(Request $request)
    {
        $search = $request->get('search'); // الحصول على النص المدخل
        $users = User::where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->paginate(10); // تقسيم النتائج
    
        // التحقق من وجود نتائج
        if ($users->total() > 0) {
            return response()->json([
                'status' => 'success',
                'users' => $users
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No users found'
            ], 404);
        }
    }
    
    public function users_event(Request $request)
    {
        if(auth()->user()->is_admin === true||auth()->user()->id == $request->user_id)
        {
            $event_id = $request->event_id; // الحصول على الرقم التعريفي للحدث
            $users = User::whereHas('events', function ($query) use ($event_id) {
            $query->where('event_id', $event_id);
            })->paginate(10); // تقسيم النتا��ج
            if ($users->total() > 0) {
                return response()->json([
                   'status' =>'success',
                    'users' => $users
                ], 200);
            } else {
                return response()->json([
                   'status' => 'error',
                   'message' => 'No users found'
                ], 404);
            }
         
        }  

    }

}
