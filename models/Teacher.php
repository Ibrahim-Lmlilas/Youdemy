<?php

require_once __DIR__ . '/abstracts/User.php';
require_once __DIR__ . '/../config/Database.php';

/**
 * Had class khas b Teacher (Ostad) f system
 * Fih ga3 functions li kay7taj prof: tchof courses dyalo, students dyalo...
 */
class Teacher extends User {
    private $courses = [];

    // Setters
    public function setId($id) {
        parent::setId($id); // Call parent setId
    }

    public function setName($name) {
        parent::setName($name); // Call parent setName
    }

    public function setEmail($email) {
        parent::setEmail($email); // Call parent setEmail
    }

    public function setStatus($status) {
        parent::setStatus($status); // Call parent setStatus
    }

    /**
     * Kat3tik role dyal had user
     * Khassna n3rfo wach student wla teacher bach n3rfo permissions dyalo
     */
    public function getRole() {
        return 'teacher';
    }

    /**
     * Kat3tik permissions dyal prof
     * 3ndo l7a9 bach: yzid cours, y3dl cours, y7ydo, w ychof students dyalo
     */
    public function getPermissions() {
        return [
            'can_create_course' => true,
            'can_edit_course' => true,
            'can_delete_course' => true,
            'can_view_students' => true
        ];
    }

    /**
     * Kat3tik courses dyal teacher
     */
    public function getCourses() {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $query = "SELECT * FROM courses WHERE teacher_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$this->id]);
            
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug
            error_log("Teacher ID: " . $this->id);
            error_log("Courses found: " . print_r($courses, true));
            
            return $courses;
        } catch(PDOException $e) {
            error_log("Error in getCourses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Katjib ga3 cours m3a id
     * @param int $courseId Id dyal cours
     * @return array Cours m3a id
     */
    public function getCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT c.*, cat.name as category_name 
                    FROM courses c
                    LEFT JOIN categories cat ON c.category_id = cat.id
                    WHERE c.id = :id AND c.teacher_id = :teacher_id";
            
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

    /**
     * Create cours jdid
     * @param array $data Data dyal cours
     * @return bool True wla false
     */
    public function createCourse($data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            // Start transaction
            $conn->beginTransaction();

            // Insert course
            $query = "INSERT INTO courses (teacher_id, category_id, title, type, description, status, url, document_path) 
                     VALUES (:teacher_id, :category_id, :title, :type, :description, :status, :url, :document_path)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':teacher_id', $this->id);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':status', $data['status'] ?? 'draft');
            $stmt->bindParam(':url', $data['type'] === 'video' ? $data['url'] : null);
            $stmt->bindParam(':document_path', $data['type'] === 'document' ? $data['document_path'] : null);
            
            if (!$stmt->execute()) {
                $conn->rollBack();
                return false;
            }

            $course_id = $conn->lastInsertId();

            // Insert course tags
            if (!empty($data['tag_ids'])) {
                $query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
                $stmt = $conn->prepare($query);

                foreach ($data['tag_ids'] as $tag_id) {
                    $stmt->bindParam(':course_id', $course_id);
                    $stmt->bindParam(':tag_id', $tag_id);
                    
                    if (!$stmt->execute()) {
                        $conn->rollBack();
                        return false;
                    }
                }
            }

            // Commit transaction
            $conn->commit();
            return true;

        } catch(PDOException $e) {
            error_log("Error in createCourse: " . $e->getMessage());
            if ($conn) {
                $conn->rollBack();
            }
            return false;
        }
    }

    /**
     * Update had prof
     * @return bool True wla false
     */
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

    /**
     * Delete had prof
     * @return bool True wla false
     */
    public function delete() {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'");
        return $stmt->execute([$this->id]);
    }

    /**
     * Katjib ga3 les students li kaydiro cours m3a had prof
     * @param int $courseId Id dyal cours
     * @return array Lista dyal les students
     */
    public function getCourseStudents($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT u.id, u.name, u.email,
                   e.status, e.progress, e.enrolled_at, e.completed_at
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            WHERE e.course_id = ? AND u.role = 'student'
            ORDER BY e.enrolled_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$courseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    /**
     * Add content jdid l cours (video/pdf)
     * @param int $courseId Id dyal cours
     * @param array $data Data dyal content
     * @return bool True wla false
     */
    public function addCourseContent($courseId, $data) {
        try {
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
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Update course
     * @param int $courseId Id dyal cours
     * @param array $data Data jdida
     * @return bool True wla false
     */
    public function updateCourse($courseId, $data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("
                UPDATE courses 
                SET category_id = ?, 
                    title = ?, 
                    type = ?,
                    description = ?, 
                    status = ?,
                    url = ?,
                    document_path = ?
                WHERE id = ? AND teacher_id = ?
            ");
            
            return $stmt->execute([
                $data['category_id'],
                $data['title'],
                $data['type'],
                $data['description'],
                $data['status'] ?? 'draft',
                $data['type'] === 'video' ? $data['url'] : null,
                $data['type'] === 'document' ? $data['document_path'] : null,
                $courseId,
                $this->id
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Delete course
     * @param int $courseId Id dyal cours
     * @return bool True wla false
     */
    public function deleteCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("
                DELETE FROM courses 
                WHERE id = ? AND teacher_id = ?
            ");
            
            return $stmt->execute([$courseId, $this->id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Katjib ga3 categories
     */
    public function getCategories() {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $query = "SELECT * FROM categories ORDER BY name ASC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getCategories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Katjib ga3 tags
     */
    public function getTags() {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $query = "SELECT * FROM tags ORDER BY name ASC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getTags: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new tag
     */
    public function createTag($name) {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            // Check if tag already exists
            $query = "SELECT id FROM tags WHERE name = :name";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            
            if ($tag = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $tag['id'];
            }

            // Create new tag
            $query = "INSERT INTO tags (name) VALUES (:name)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            
            if ($stmt->execute()) {
                return $conn->lastInsertId();
            }
            
            return false;
        } catch(PDOException $e) {
            error_log("Error in createTag: " . $e->getMessage());
            return false;
        }
    }
}
