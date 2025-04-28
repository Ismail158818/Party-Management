<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Http\Services\Fun_Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use App\Traits\ApiResponse;

class EventApiController extends Controller
{
    use ApiResponse;

    /**
     * عرض جميع الأحداث
     */
    public function index()
{
    $events = Event::paginate(10);

    if ($events->isEmpty()) {
        return $this->error('No events found', 404);
    }

    return $this->success(EventResource::collection($events), 'Events retrieved successfully');
}


    /**
     * إضافة حدث جديد
     */
   // داخل الكنترولر:
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'media' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov',
        'date' => 'required|date',
        'location' => 'required|string|max:255',
        'tickect_price' => 'required|integer',
        'tickets_limit' => 'required|integer'
    ]);

    

    if (Event::where('title', $validated['title'])->exists()) {
        return $this->error('Event with this title already exists', 409);
    }

    $store = new Fun_Event();
    $data = $store->store($validated);

    if (!$data) {
        return $this->error('Failed to create event', 500);
    }

    return $this->success(new EventResource($data), 'Event created successfully', 201);
}


    /**
     * عرض حدث معين حسب العنوان والتاريخ
     */
    public function show(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'date' => 'nullable|date',
        ]);

        $query = Event::query();

        if (!empty($validated['title'])) {
            $query->where('title', 'like', '%' . $validated['title'] . '%');
        }

        if (!empty($validated['date'])) {
            $query->whereDate('date', '=', $validated['date']);
        }

        $events = $query->get();

        if ($events->isEmpty()) {
            return $this->error('No results found', 404);
        }

        return $this->success(EventResource::collection($events), 'Events found');
    }

    /**
     * تحديث حدث
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:events,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'ticket_price' => 'required|integer',
            'tickets_limit' => 'required|integer'
        ]);

        $event = Event::find($validated['id']);

        if (!$event) {
            return $this->error('Event not found', 404);
        }

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'media' => $validated['media'] ?? $event->media,
            'date' => $validated['date'],
            'location' => $validated['location'],
            'ticket_price' => $validated['ticket_price'],
            'tickets_limit' => $validated['tickets_limit']
        ]);

        return $this->success(new EventResource($event), 'Event updated successfully');
    }

    /**
     * حذف حدث
     */
    public function destroy(Request $request)
    {
    // البحث عن الحدث باستخدام الـ id
    $event = Event::find($request['id']);

    // إذا لم يتم العثور على الحدث، ارجع برسالة خطأ
    if (!$event) {
        return $this->error('Event not found', 404);
    }

    // إذا كان الحدث مرتبطًا بمستخدمين، فك الارتباط أولًا
    if ($event->users()->count() > 0) {
        $event->users()->detach(); // فك الارتباط
    }

    // حذف الحدث من قاعدة البيانات
    $event->delete();

    // إرجاع استجابة ناجحة بعد الحذف
    return $this->success(null, 'Event deleted successfully');
    }

}
