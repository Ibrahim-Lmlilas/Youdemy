<?php

require_once __DIR__ . '/../core/abstracts/Course.php';

class TeacherCourse extends Course {
    public function __construct($db) {
        parent::__construct($db);
    }

    public function save() {
        $stmt = $this->db->prepare("
            INSERT INTO courses (title, description, content, category_id, teacher_id, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $this->getTitle(),
            $this->getDescription(),
            $this->getContent(),
            $this->getCategoryId(),
            $this->getTeacherId(),
            $this->getStatus() ?? 'draft'
        ]);
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

    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, GROUP_CONCAT(t.id) as tag_ids
            FROM courses c
            LEFT JOIN course_tags ct ON c.id = ct.course_id
            LEFT JOIN tags t ON ct.tag_id = t.id
            WHERE c.id = ?
            GROUP BY c.id
        ");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($course) {
            foreach ($course as $key => $value) {
                $setter = 'set' . ucfirst($key);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
            return true;
        }
        return false;
    }

    public function findAll() {
        return $this->getAllByTeacher($this->getTeacherId());
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

    public function getAllByTeacher($teacherId) {
        $stmt = $this->db->prepare("
            SELECT c.*, GROUP_CONCAT(t.id) as tag_ids, GROUP_CONCAT(t.name) as tag_names
            FROM courses c
            LEFT JOIN course_tags ct ON c.id = ct.course_id
            LEFT JOIN tags t ON ct.tag_id = t.id
            WHERE c.teacher_id = ?
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
