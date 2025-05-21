<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Course;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\AnnouncementResource;

class AnnouncementController extends Controller
{
    /**
     * GET /api/announcements
     * List announcements for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->isStudent()) {
            $limit = $request->get('limit', 10);
            $detailed = $request->boolean('detailed', true);
            $announcements = Announcement::getStudentAnnouncements($user, $limit, $detailed);
            return response()->json($announcements);
        }

        // For instructors, show announcements for their courses
        $announcements = Announcement::query()
            ->forCourses($user->instructorCourses()->pluck('id'))
            ->orderByPinnedAndDate()
            ->with(['course:id,title', 'creator:id,name,role'])
            ->paginate(15);

        return AnnouncementResource::collection($announcements);
    }

    /**
     * GET /api/student/announcements
     * Get recent announcements for the student's enrolled courses
     */
    public function studentAnnouncements(Request $request)
    {
        $user = $request->user();
        $announcements = Announcement::getStudentAnnouncements($user);
        return $this->respond($announcements);
    }

    /**
     * GET /api/courses/{course}/announcements
     * Get announcements for a specific course
     */
    public function courseAnnouncements(Request $request, Course $course)
    {
        $this->authorize('view', $course);

        $announcements = $course->announcements()
            ->orderByPinnedAndDate()
            ->with('creator:id,name,role')
            ->paginate(15);

        return AnnouncementResource::collection($announcements);
    }

    /**
     * POST /api/courses/{course}/announcements
     * Create a new announcement
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('create', [Announcement::class, $course]);

        $data = $this->validated($request, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_pinned' => 'boolean',
        ]);

        $announcement = Announcement::createAnnouncement(
            $data,
            $course->id,
            $request->user()->id
        );

        return new AnnouncementResource($announcement);
    }

    /**
     * PUT /api/announcements/{announcement}
     * Update an announcement
     */
    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $data = $this->validated($request, [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'is_pinned' => 'boolean',
        ]);

        $announcement->update($data);

        return new AnnouncementResource($announcement);
    }

    /**
     * DELETE /api/announcements/{announcement}
     * Delete an announcement (instructor only)
     */
    public function destroy(Announcement $announcement)
    {
        $this->authorize('update', $announcement->course);

        $announcement->delete();

        return $this->respond(null, 'Announcement deleted', Response::HTTP_NO_CONTENT);
    }
} 