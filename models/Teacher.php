<?php

require_once __DIR__ . '/abstracts/User.php';
require_once __DIR__ . '/../config/Database.php';


class Teacher extends User {
    private $courses = [];


    public function getRole() {
        return 'teacher';
    }

    public function getPermissions() {
        return [
            'can_create_course' => true,
            'can_edit_course' => true,
            'can_delete_course' => true,
            'can_view_students' => true
        ];
    }


    public function getCourses() {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $query = "SELECT c.*, GROUP_CONCAT(t.name) as tags 
                     FROM courses c 
                     LEFT JOIN course_tags ct ON c.id = ct.course_id 
                     LEFT JOIN tags t ON ct.tag_id = t.id 
                     WHERE c.teacher_id = ? 
                     GROUP BY c.id";
            $stmt = $conn->prepare($query);
            $stmt->execute([$this->id]);
            
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($courses as &$course) {
                $course['tags'] = $course['tags'] ? explode(',', $course['tags']) : [];
            }
            

            return $courses;
        } catch(PDOException $e) {
            error_log("Error in getCourses: " . $e->getMessage());
            return [];
        }
    }


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


    public function createCourse($data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $conn->beginTransaction();

            $query = "INSERT INTO courses (teacher_id, category_id, title, type, description, status, url, document_path)
                     VALUES (:teacher_id, :category_id, :title, :type, :description, :status, :url, :document_path)";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                'teacher_id' => $this->id,
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'type' => $data['type'],
                'description' => $data['description'],
                'status' => $data['status'],
                'url' => $data['url'],
                'document_path' => $data['document_path']
            ]);

            $course_id = $conn->lastInsertId();

            if (!empty($data['tags'])) {
                $tag_query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
                $tag_stmt = $conn->prepare($tag_query);
                
                foreach ($data['tags'] as $tag_id) {
                    $tag_stmt->execute([
                        'course_id' => $course_id,
                        'tag_id' => $tag_id
                    ]);
                }
            }

            $conn->commit();
            return true;

        } catch(PDOException $e) {
            $conn->rollBack();
            error_log("Error in createCourse: " . $e->getMessage());
            return false;
        }
    }

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

     // Katjib ga3 tags
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

    public function getCourseTags($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $query = "SELECT ct.tag_id, t.name 
                     FROM course_tags ct 
                     JOIN tags t ON ct.tag_id = t.id 
                     WHERE ct.course_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$courseId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error in getCourseTags: " . $e->getMessage());
            return [];
        }
    }

    public function updateCourse($courseId, $data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $conn->beginTransaction();

            // Update course details
            $query = "UPDATE courses 
                     SET category_id = :category_id,
                         title = :title,
                         type = :type,
                         description = :description,
                         status = :status,
                         url = :url,
                         document_path = :document_path
                     WHERE id = :id AND teacher_id = :teacher_id";
            
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'type' => $data['type'],
                'description' => $data['description'],
                'status' => $data['status'],
                'url' => $data['url'],
                'document_path' => $data['document_path'],
                'id' => $courseId,
                'teacher_id' => $this->id
            ]);

            if (!$result) {
                throw new Exception("Failed to update course");
            }

            $stmt = $conn->prepare("DELETE FROM course_tags WHERE course_id = ?");
            $stmt->execute([$courseId]);

            if (!empty($data['tags'])) {
                $tag_query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
                $tag_stmt = $conn->prepare($tag_query);
                
                foreach ($data['tags'] as $tag_id) {
                    $tag_stmt->execute([
                        'course_id' => $courseId,
                        'tag_id' => $tag_id
                    ]);
                }
            }

            $conn->commit();
            return true;

        } catch(Exception $e) {
            $conn->rollBack();
            error_log("Error in updateCourse: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCourse($courseId) {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $query = "SELECT id FROM courses WHERE id = ? AND teacher_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$courseId, $this->id]);
            
            if (!$stmt->fetch()) {
                return false; 
            }

            $query = "DELETE FROM courses WHERE id = ?";
            $stmt = $conn->prepare($query);
            $result = $stmt->execute([$courseId]);
            
            return $result;
        } catch(PDOException $e) {
            error_log("Error in deleteCourse: " . $e->getMessage());
            return false;
        }
    }
}
