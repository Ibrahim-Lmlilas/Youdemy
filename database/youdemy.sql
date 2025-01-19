CREATE DATABASE  YooudemY;

USE YooudemY;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    status ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE Table tags(
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE ,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE Table courses(
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    type ENUM('video', 'document'),
    description TEXT,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);


CREATE Table course_tags(
    course_id int(11) NOT NULL,
    tag_id int(11) NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

CREATE Table enrollments(
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Password: HAKARI
INSERT INTO users (name, email, password, role, status) VALUES
('Admin User', 'admin@youdemy.com', '$2y$10$mcqddzUGxr.hu1LxlP9DZelrmmWveclZZafbOAYAXDvwER7EElQYO', 'admin', 'active'),
('Teacher One', 'teacher1@youdemy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
('Student One', 'student1@youdemy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active');

INSERT INTO categories (name, description) VALUES
('Web Development', 'Learn web development from scratch'),
('Mobile Development', 'Create mobile apps for iOS and Android'),
('Data Science', 'Master data science and machine learning');

INSERT INTO tags (name) VALUES
('PHP'),
('JavaScript'),
('Python');

INSERT INTO courses (teacher_id, category_id, title, type, description, status) VALUES
(2, 1, 'PHP Fundamentals', 'video', 'Learn PHP basics and advanced concepts', 'published'),
(2, 1, 'JavaScript Mastery', 'video', 'Master JavaScript programming', 'published'),
(2, 2, 'Mobile App Development', 'document', 'Create your first mobile app', 'published'),
(2, 3, 'Python for Data Science', 'video', 'Learn Python for data analysis', 'draft');

INSERT INTO course_tags (course_id, tag_id) VALUES
(1, 1), 
(2, 2);

INSERT INTO enrollments (student_id, course_id) VALUES
(2, 1), 
(2, 2); 

ALTER TABLE courses 
ADD COLUMN url VARCHAR(255) AFTER description,
ADD COLUMN document_path VARCHAR(255) AFTER url;

UPDATE courses 
SET url = CASE 
    WHEN type = 'video' THEN 'https://www.youtube.com/watch?v=example'
    ELSE ''
END,
document_path = CASE 
    WHEN type = 'document' THEN '/uploads/courses/documents/example.pdf'
    ELSE ''
END;
