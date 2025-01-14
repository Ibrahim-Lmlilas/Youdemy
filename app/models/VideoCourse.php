<?php

class VideoCourse extends Course {
    private $videoUrl;
    private $duration;

    public function __construct() {
        parent::__construct();
        $this->type = 'video';
    }

    public function setVideoUrl($url) {
        $this->videoUrl = $url;
    }

    public function setDuration($duration) {
        $this->duration = $duration;
    }

    public function save() {
        $sql = "INSERT INTO courses (title, description, content, status, type, teacher_id, category_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $content = json_encode([
            'videoUrl' => $this->videoUrl,
            'duration' => $this->duration
        ]);

        return $this->db->query($sql, [
            $this->title,
            $this->description,
            $content,
            $this->status,
            $this->type,
            $_SESSION['user_id'],
            1 // Default category for now
        ]);
    }

    public function update() {
        $sql = "UPDATE courses 
                SET title = ?, description = ?, content = ?, status = ? 
                WHERE id = ? AND teacher_id = ?";
        
        $content = json_encode([
            'videoUrl' => $this->videoUrl,
            'duration' => $this->duration
        ]);

        return $this->db->query($sql, [
            $this->title,
            $this->description,
            $content,
            $this->status,
            $this->id,
            $_SESSION['user_id']
        ]);
    }

    public function delete() {
        $sql = "DELETE FROM courses WHERE id = ? AND teacher_id = ?";
        return $this->db->query($sql, [$this->id, $_SESSION['user_id']]);
    }

    public function getContent() {
        if (!empty($this->content)) {
            $data = json_decode($this->content, true);
            return [
                'videoUrl' => $data['videoUrl'] ?? '',
                'duration' => $data['duration'] ?? ''
            ];
        }
        return null;
    }
}
