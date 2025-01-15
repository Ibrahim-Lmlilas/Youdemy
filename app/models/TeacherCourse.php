<?php

require_once __DIR__ . '/../core/abstracts/Course.php';

class TeacherCourse extends Course {
    public function __construct() {
        parent::__construct();
    }

    public function save() {
        if ($this->id) {
            // Update existing course
            $sql = "UPDATE courses SET 
                    title = ?, 
                    description = ?, 
                    content = ?,
                    type = ?,
                    content_url = ?,
                    category_id = ?,
                    status = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $this->title,
                $this->description,
                $this->content,
                $this->type,
                $this->content_url,
                $this->category_id,
                $this->status,
                $this->id
            ]);
        } else {
            // Create new course
            $sql = "INSERT INTO courses (
                    title, description, content, type, content_url,
                    category_id, teacher_id, status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $this->title,
                $this->description,
                $this->content,
                $this->type,
                $this->content_url,
                $this->category_id,
                $this->teacher_id,
                $this->status
            ]);

            if ($result) {
                $this->id = $this->db->lastInsertId();
                return true;
            }
            return false;
        }
    }

    public function delete() {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Delete from course_tags
            $stmt = $this->db->prepare("DELETE FROM course_tags WHERE course_id = ?");
            $stmt->execute([$this->getId()]);

            // Delete from enrollments
            $stmt = $this->db->prepare("DELETE FROM enrollments WHERE course_id = ?");
            $stmt->execute([$this->getId()]);

            // Delete from comments
            $stmt = $this->db->prepare("DELETE FROM comments WHERE course_id = ?");
            $stmt->execute([$this->getId()]);

            // Delete from notifications related to this course
            $stmt = $this->db->prepare("DELETE FROM notifications WHERE reference_id = ? AND type LIKE 'course%'");
            $stmt->execute([$this->getId()]);

            // Finally delete from courses
            $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
            $success = $stmt->execute([$this->getId(), $this->getTeacherId()]);

            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findAll() {
        $sql = "SELECT c.*, GROUP_CONCAT(t.name) as tags
                FROM courses c
                LEFT JOIN course_tags ct ON c.id = ct.course_id
                LEFT JOIN tags t ON ct.tag_id = t.id
                GROUP BY c.id
                ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeacherCourses($teacherId) {
        $sql = "SELECT c.*, GROUP_CONCAT(t.name) as tags
                FROM courses c
                LEFT JOIN course_tags ct ON c.id = ct.course_id
                LEFT JOIN tags t ON ct.tag_id = t.id
                WHERE c.teacher_id = ?
                GROUP BY c.id
                ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveCourses($teacherId) {
        $sql = "SELECT COUNT(*) as active_count FROM courses WHERE teacher_id = ? AND status = 'published'";
        $result = $this->db->query($sql, [$teacherId])->fetch();
        return $result['active_count'];
    }

    public function getTotalCourses($teacherId) {
        $sql = "SELECT COUNT(*) as total_count FROM courses WHERE teacher_id = ?";
        $result = $this->db->query($sql, [$teacherId])->fetch();
        return $result['total_count'];
    }

    public function update() {
        $stmt = $this->db->prepare("
            UPDATE courses 
            SET title = ?, description = ?, content = ?,
                category_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND teacher_id = ?
        ");
        
        return $stmt->execute([
            $this->getTitle(),
            $this->getDescription(),
            $this->getContent(),
            $this->getCategoryId(),
            $this->getStatus() ?? 'draft',
            $this->getId(),
            $this->getTeacherId()
        ]);
    }

    public function updateTags($tagIds) {
        // Delete existing tags
        $stmt = $this->db->prepare("DELETE FROM course_tags WHERE course_id = ?");
        $stmt->execute([$this->getId()]);
        
        // Insert new tags
        if (!empty($tagIds)) {
            $values = array_fill(0, count($tagIds), "(?, ?)");
            $sql = "INSERT INTO course_tags (course_id, tag_id) VALUES " . implode(", ", $values);
            
            $params = [];
            foreach ($tagIds as $tagId) {
                $params[] = $this->getId();
                $params[] = $tagId;
            }
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
        return true;
    }
}
?>
