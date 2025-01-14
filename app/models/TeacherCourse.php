<?php

require_once __DIR__ . '/../core/abstracts/Course.php';

class TeacherCourse extends Course {
    public function save() {
        $stmt = $this->db->prepare("
            INSERT INTO courses (title, description, content, image, price, category_id, teacher_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $this->title,
            $this->description,
            $this->content,
            $this->image,
            $this->price,
            $this->category_id,
            $this->teacher_id,
            $this->status
        ]);
    }

    public function update() {
        $stmt = $this->db->prepare("
            UPDATE courses 
            SET title = ?, description = ?, content = ?, image = ?,
                price = ?, category_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND teacher_id = ?
        ");
        
        return $stmt->execute([
            $this->title,
            $this->description,
            $this->content,
            $this->image,
            $this->price,
            $this->category_id,
            $this->status,
            $this->id,
            $this->teacher_id
        ]);
    }

    public function delete() {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
        return $stmt->execute([$this->id, $this->teacher_id]);
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($course) {
            $this->id = $course['id'];
            $this->title = $course['title'];
            $this->description = $course['description'];
            $this->content = $course['content'];
            $this->image = $course['image'];
            $this->price = $course['price'];
            $this->category_id = $course['category_id'];
            $this->teacher_id = $course['teacher_id'];
            $this->status = $course['status'];
            $this->created_at = $course['created_at'];
            $this->updated_at = $course['updated_at'];
            return true;
        }
        return false;
    }

    public function findAll() {
        $stmt = $this->db->prepare("
            SELECT c.*, cat.name as category_name, u.name as teacher_name,
                   COUNT(DISTINCT e.id) as enrollment_count,
                   COUNT(DISTINCT com.id) as comment_count
            FROM courses c
            LEFT JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN users u ON c.teacher_id = u.id
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN comments com ON c.id = com.course_id
            WHERE c.teacher_id = ?
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        
        $stmt->execute([$this->teacher_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Methods specific to TeacherCourse
    public function publish() {
        $stmt = $this->db->prepare("UPDATE courses SET status = 'published' WHERE id = ? AND teacher_id = ?");
        return $stmt->execute([$this->id, $this->teacher_id]);
    }

    public function archive() {
        $stmt = $this->db->prepare("UPDATE courses SET status = 'archived' WHERE id = ? AND teacher_id = ?");
        return $stmt->execute([$this->id, $this->teacher_id]);
    }

    public function getStudents() {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email, e.progress, e.status, e.enrolled_at
            FROM users u
            JOIN enrollments e ON u.id = e.student_id
            WHERE e.course_id = ? AND u.role = 'student'
            ORDER BY e.enrolled_at DESC
        ");
        
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatistics() {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT e.id) as total_students,
                AVG(e.progress) as average_progress,
                COUNT(DISTINCT CASE WHEN e.status = 'completed' THEN e.id END) as completed_count,
                COUNT(DISTINCT com.id) as total_comments
            FROM courses c
            LEFT JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN comments com ON c.id = com.course_id
            WHERE c.id = ? AND c.teacher_id = ?
            GROUP BY c.id
        ");
        
        $stmt->execute([$this->id, $this->teacher_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
