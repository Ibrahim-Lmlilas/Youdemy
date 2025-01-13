<?php

require_once '../core/abstracts/User.php';

class Student extends User {
    private $enrolledCourses = [];

    public function getRole() {
        return 'student';
    }

    public function getDashboard() {
        return [
            'enrolled_courses' => $this->enrolledCourses,
            'progress' => $this->getProgress()
        ];
    }

    public function getPermissions() {
        return [
            'can_enroll' => true,
            'can_view_courses' => true,
            'can_submit_reviews' => true
        ];
    }

    public function enrollCourse($courseId) {
        // Here we'll add enrollment logic
        $this->enrolledCourses[] = $courseId;
        return true;
    }

    private function getProgress() {
        // Here we'll calculate progress for enrolled courses
        return [];
    }
}
