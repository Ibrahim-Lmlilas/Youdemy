<?php

require_once __DIR__ . '/../core/abstracts/User.php';
require_once __DIR__ . '/../config/Database.php';

class Teacher extends User {
    private $courses = [];

    public function getRole() {
        return 'teacher';
    }

    public function getDashboard() {
        return [
            'my_courses' => $this->getCourses(),
            'total_students' => $this->getTotalStudents(),
            'course_stats' => $this->getCourseStats()
        ];
    }

    public function getPermissions() {
        return [
            'can_create_course' => true,
            'can_edit_course' => true,
            'can_delete_course' => true,
            'can_view_students' => true
        ];
    }

    // Course Management
    public function createCourse($data) {
        try {
            $course = new TeacherCourse($this->db);
            $course->setTitle($data['title']);
            $course->setDescription($data['description']);
            $course->setContent($data['content']);
            $course->setTeacherId($this->id);
            $course->setStatus('draft');
            
            return $course->save();
        } catch(Exception $e) {
            return false;
        }
    }

    public function updateCourse($courseId, $data) {
        try {
            $course = new TeacherCourse($this->db);
            $course->setId($courseId);
            $course->setTitle($data['title']);
            $course->setDescription($data['description']);
            $course->setContent($data['content']);
            $course->setTeacherId($this->id);
            $course->setStatus($data['status']);
            
            return $course->update();
        } catch(Exception $e) {
            return false;
        }
    }

    public function deleteCourse($courseId) {
        try {
            $course = new TeacherCourse($this->db);
            $course->setId($courseId);
            $course->setTeacherId($this->id);
            
            return $course->delete();
        } catch(Exception $e) {
            return false;
        }
    }

    // Course Retrieval
    public function getCourses() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT * FROM courses WHERE teacher_id = :teacher_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['teacher_id' => $this->id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT * FROM courses 
                    WHERE id = :id AND teacher_id = :teacher_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'id' => $courseId,
                'teacher_id' => $this->id
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return null;
        }
    }

    // Student Management
    public function getStudentsInCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT u.* FROM users u 
                    JOIN enrollments e ON u.id = e.student_id 
                    JOIN courses c ON e.course_id = c.id 
                    WHERE c.id = :course_id AND c.teacher_id = :teacher_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'course_id' => $courseId,
                'teacher_id' => $this->id
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    private function getTotalStudents() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT COUNT(DISTINCT student_id) as total 
                    FROM enrollments e 
                    JOIN courses c ON e.course_id = c.id 
                    WHERE c.teacher_id = :teacher_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute(['teacher_id' => $this->id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] ?? 0;
        } catch(PDOException $e) {
            return 0;
        }
    }

    // Statistics
    private function getCourseStats() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT c.id, c.title, 
                    COUNT(e.student_id) as enrolled_students,
                    AVG(e.progress) as avg_progress
                    FROM courses c 
                    LEFT JOIN enrollments e ON c.id = e.course_id 
                    WHERE c.teacher_id = :teacher_id 
                    GROUP BY c.id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute(['teacher_id' => $this->id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    public function update() {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("
            UPDATE users 
            SET name = ?, email = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND role = 'teacher'
        ");
        return $stmt->execute([$this->name, $this->email, $this->status, $this->id]);
    }

    public function delete() {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'");
        return $stmt->execute([$this->id]);
    }

    public function createCourseNew($data) {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("
            INSERT INTO courses (teacher_id, category_id, title, description, content, image, price, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'draft')
        ");
        
        return $stmt->execute([
            $this->id,
            $data['category_id'],
            $data['title'],
            $data['description'],
            $data['content'],
            $data['image'] ?? null,
            $data['price'] ?? 0
        ]);
    }

    public function getMyCoursesNew() {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("
            SELECT c.*, cat.name as category_name,
                   COUNT(DISTINCT e.id) as total_students,
                   COUNT(DISTINCT com.id) as total_comments
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN comments com ON c.id = com.course_id
            WHERE c.teacher_id = ?
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseStudentsNew($courseId) {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("
            SELECT u.id, u.name, u.email,
                   e.status, e.progress, e.enrolled_at, e.completed_at
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            WHERE e.course_id = ? AND u.role = 'student'
            ORDER BY e.enrolled_at DESC
        ");
        
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseStatisticsNew($courseId = null) {
        $db = new Database();
        $conn = $db->getConnection();
        $query = "
            SELECT 
                COUNT(DISTINCT e.id) as total_enrollments,
                COUNT(DISTINCT CASE WHEN e.status = 'completed' THEN e.id END) as completed_count,
                COUNT(DISTINCT CASE WHEN e.status = 'active' THEN e.id END) as active_count,
                COUNT(DISTINCT CASE WHEN e.status = 'dropped' THEN e.id END) as dropped_count,
                AVG(e.progress) as average_progress,
                COUNT(DISTINCT com.id) as total_comments
            FROM courses c
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN comments com ON c.id = com.course_id
            WHERE c.teacher_id = ?
        ";
        
        if($courseId) {
            $query .= " AND c.id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$this->id, $courseId]);
        } else {
            $stmt = $conn->prepare($query);
            $stmt->execute([$this->id]);
        }
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addCourseContent($courseId, $data) {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("
            INSERT INTO course_content (course_id, title, type, content_url, duration, order_number)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $courseId,
            $data['title'],
            $data['type'],
            $data['content_url'],
            $data['duration'] ?? 0,
            $data['order_number'] ?? 0
        ]);
    }

    public function getCourseContent($courseId) {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("
            SELECT *
            FROM course_content
            WHERE course_id = ?
            ORDER BY order_number ASC
        ");
        
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
