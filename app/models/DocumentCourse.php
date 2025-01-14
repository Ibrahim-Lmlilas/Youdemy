<?php

class DocumentCourse extends Course {
    private $documentUrl;
    private $pageCount;

    public function __construct() {
        parent::__construct();
        $this->type = 'document';
    }

    public function setDocumentUrl($url) {
        $this->documentUrl = $url;
    }

    public function setPageCount($count) {
        $this->pageCount = $count;
    }

    public function save() {
        try {
            $sql = "INSERT INTO courses (title, description, content, status, type, teacher_id, category_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $content = json_encode([
                'documentUrl' => $this->documentUrl,
                'pageCount' => $this->pageCount
            ]);

            $stmt = $this->db->query($sql, [
                $this->title,
                $this->description,
                $content,
                $this->status,
                $this->type,
                $_SESSION['user_id'],
                1 // Default category for now
            ]);

            return true;
        } catch (Exception $e) {
            error_log("Error saving document course: " . $e->getMessage());
            return false;
        }
    }

    public function update() {
        try {
            $sql = "UPDATE courses 
                    SET title = ?, description = ?, content = ?, status = ? 
                    WHERE id = ? AND teacher_id = ?";
            
            $content = json_encode([
                'documentUrl' => $this->documentUrl,
                'pageCount' => $this->pageCount
            ]);

            $stmt = $this->db->query($sql, [
                $this->title,
                $this->description,
                $content,
                $this->status,
                $this->id,
                $_SESSION['user_id']
            ]);

            return true;
        } catch (Exception $e) {
            error_log("Error updating document course: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        try {
            $sql = "DELETE FROM courses WHERE id = ? AND teacher_id = ?";
            $stmt = $this->db->query($sql, [$this->id, $_SESSION['user_id']]);
            return true;
        } catch (Exception $e) {
            error_log("Error deleting document course: " . $e->getMessage());
            return false;
        }
    }

    public function getContent() {
        if (!empty($this->content)) {
            $data = json_decode($this->content, true);
            return [
                'documentUrl' => $data['documentUrl'] ?? '',
                'pageCount' => $data['pageCount'] ?? ''
            ];
        }
        return null;
    }
}
