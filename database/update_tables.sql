-- Add theme_mode column to user_settings table
ALTER TABLE user_settings ADD COLUMN theme_mode ENUM('light', 'dark') DEFAULT 'light';

-- Create note_views table
CREATE TABLE IF NOT EXISTS note_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    note_id INT NOT NULL,
    user_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create comments table
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create note_comments table
CREATE TABLE IF NOT EXISTS note_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    note_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create video_comments table
CREATE TABLE IF NOT EXISTS video_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    video_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_comments table
CREATE TABLE IF NOT EXISTS course_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('friend_request', 'friend_accepted', 'course_enrolled', 'course_created', 'post_liked', 'post_commented', 'video_liked', 'video_commented') NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create user_settings table if it doesn't exist
CREATE TABLE IF NOT EXISTS user_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    email_notifications BOOLEAN DEFAULT TRUE,
    profile_visibility ENUM('public', 'friends', 'private') DEFAULT 'public',
    theme_mode ENUM('light', 'dark') DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add user_id column to comments table if it doesn't exist
ALTER TABLE comments ADD COLUMN IF NOT EXISTS user_id INT NOT NULL AFTER id;
ALTER TABLE comments ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Create course_reviews table
CREATE TABLE IF NOT EXISTS course_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    rating DECIMAL(2,1) NOT NULL,
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_review (course_id, user_id)
);

-- Create course_enrollments table if not exists
CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (course_id, user_id)
);

-- Create courses table if not exists
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    instructor_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    price DECIMAL(10,2) DEFAULT 0.00,
    thumbnail VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_lessons table if not exists
CREATE TABLE IF NOT EXISTS course_lessons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_url VARCHAR(255),
    duration INT, -- in minutes
    order_index INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_comments table if not exists
CREATE TABLE IF NOT EXISTS course_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_progress table if not exists
CREATE TABLE IF NOT EXISTS course_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (user_id, course_id, lesson_id)
);

-- Create course_quizzes table if not exists
CREATE TABLE IF NOT EXISTS course_quizzes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    lesson_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

-- Create quiz_questions table if not exists
CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_answer CHAR(1) NOT NULL,
    explanation TEXT,
    FOREIGN KEY (quiz_id) REFERENCES course_quizzes(id) ON DELETE CASCADE
);

-- Create quiz_attempts table if not exists
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES course_quizzes(id) ON DELETE CASCADE
);

-- Create course_certificates table if not exists
CREATE TABLE IF NOT EXISTS course_certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    certificate_url VARCHAR(255) NOT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_certificate (user_id, course_id)
);

-- Create course_discussions table if not exists
CREATE TABLE IF NOT EXISTS course_discussions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create discussion_replies table if not exists
CREATE TABLE IF NOT EXISTS discussion_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    discussion_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (discussion_id) REFERENCES course_discussions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_announcements table if not exists
CREATE TABLE IF NOT EXISTS course_announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    instructor_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_resources table if not exists
CREATE TABLE IF NOT EXISTS course_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    lesson_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    resource_url VARCHAR(255) NOT NULL,
    resource_type ENUM('document', 'video', 'audio', 'link') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

-- Create course_assignments table if not exists
CREATE TABLE IF NOT EXISTS course_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    lesson_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATETIME,
    max_score INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

-- Create assignment_submissions table if not exists
CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT NOT NULL,
    user_id INT NOT NULL,
    submission_url VARCHAR(255) NOT NULL,
    score INT,
    feedback TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES course_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (assignment_id, user_id)
);

-- Create course_ratings table if not exists
CREATE TABLE IF NOT EXISTS course_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (course_id, user_id)
);

-- Create course_favorites table if not exists
CREATE TABLE IF NOT EXISTS course_favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, course_id)
);

-- Create course_tags table if not exists
CREATE TABLE IF NOT EXISTS course_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Create course_tag_relations table if not exists
CREATE TABLE IF NOT EXISTS course_tag_relations (
    course_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (course_id, tag_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES course_tags(id) ON DELETE CASCADE
);

-- Create course_prerequisites table if not exists
CREATE TABLE IF NOT EXISTS course_prerequisites (
    course_id INT NOT NULL,
    prerequisite_id INT NOT NULL,
    PRIMARY KEY (course_id, prerequisite_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_sections table if not exists
CREATE TABLE IF NOT EXISTS course_sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    order_index INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_section_lessons table if not exists
CREATE TABLE IF NOT EXISTS course_section_lessons (
    section_id INT NOT NULL,
    lesson_id INT NOT NULL,
    order_index INT NOT NULL,
    PRIMARY KEY (section_id, lesson_id),
    FOREIGN KEY (section_id) REFERENCES course_sections(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

-- Create course_discussion_categories table if not exists
CREATE TABLE IF NOT EXISTS course_discussion_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_discussion_topics table if not exists
CREATE TABLE IF NOT EXISTS course_discussion_topics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES course_discussion_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_discussion_replies table if not exists
CREATE TABLE IF NOT EXISTS course_discussion_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    topic_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES course_discussion_topics(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_notifications table if not exists
CREATE TABLE IF NOT EXISTS course_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('announcement', 'assignment', 'discussion', 'grade') NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_analytics table if not exists
CREATE TABLE IF NOT EXISTS course_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    date DATE NOT NULL,
    total_views INT DEFAULT 0,
    total_enrollments INT DEFAULT 0,
    total_completions INT DEFAULT 0,
    average_rating DECIMAL(3,2),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_analytics (course_id, date)
);

-- Create course_events table if not exists
CREATE TABLE IF NOT EXISTS course_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    type ENUM('live_session', 'assignment_due', 'quiz', 'other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_event_reminders table if not exists
CREATE TABLE IF NOT EXISTS course_event_reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    reminder_time DATETIME NOT NULL,
    is_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES course_events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    lesson_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_url VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

-- Create course_attendance table if not exists
CREATE TABLE IF NOT EXISTS course_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (course_id, user_id, date)
);

-- Create course_grade_categories table if not exists
CREATE TABLE IF NOT EXISTS course_grade_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_grades table if not exists
CREATE TABLE IF NOT EXISTS course_grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    grade DECIMAL(5,2) NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES course_grade_categories(id) ON DELETE CASCADE
);

-- Create course_grade_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    grading_scheme ENUM('percentage', 'letter', 'pass_fail') NOT NULL,
    pass_threshold DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_grade_letters table if not exists
CREATE TABLE IF NOT EXISTS course_grade_letters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    letter VARCHAR(2) NOT NULL,
    min_percentage DECIMAL(5,2) NOT NULL,
    max_percentage DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_grade_history table if not exists
CREATE TABLE IF NOT EXISTS course_grade_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    grade_id INT NOT NULL,
    old_grade DECIMAL(5,2) NOT NULL,
    new_grade DECIMAL(5,2) NOT NULL,
    changed_by INT NOT NULL,
    change_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grade_id) REFERENCES course_grades(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_reports table if not exists
CREATE TABLE IF NOT EXISTS course_grade_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    report_url VARCHAR(255) NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_export_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_export_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    exported_by INT NOT NULL,
    export_format ENUM('csv', 'excel', 'pdf') NOT NULL,
    export_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (exported_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_import_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_import_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    imported_by INT NOT NULL,
    import_format ENUM('csv', 'excel') NOT NULL,
    import_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success_count INT DEFAULT 0,
    error_count INT DEFAULT 0,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (imported_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_audit_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    action ENUM('view', 'edit', 'delete', 'export', 'import') NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_notifications table if not exists
CREATE TABLE IF NOT EXISTS course_grade_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('grade_posted', 'grade_updated', 'grade_deleted') NOT NULL,
    grade_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (grade_id) REFERENCES course_grades(id) ON DELETE CASCADE
);

-- Create course_grade_appeals table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    grade_id INT NOT NULL,
    user_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grade_id) REFERENCES course_grades(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_comments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appeal_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appeal_id) REFERENCES course_grade_appeals(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appeal_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appeal_id) REFERENCES course_grade_appeals(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appeal_id INT NOT NULL,
    action ENUM('created', 'updated', 'status_changed', 'commented', 'attached') NOT NULL,
    details TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appeal_id) REFERENCES course_grade_appeals(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_notifications table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appeal_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('created', 'updated', 'status_changed', 'commented') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appeal_id) REFERENCES course_grade_appeals(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    allow_appeals BOOLEAN DEFAULT TRUE,
    appeal_deadline_days INT DEFAULT 7,
    require_evidence BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_categories table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_category_relations table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_category_relations (
    appeal_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (appeal_id, category_id),
    FOREIGN KEY (appeal_id) REFERENCES course_grade_appeals(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES course_grade_appeal_categories(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflows table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_steps table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_steps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workflow_id INT NOT NULL,
    step_order INT NOT NULL,
    role ENUM('student', 'instructor', 'admin') NOT NULL,
    action ENUM('review', 'approve', 'reject', 'comment') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_id) REFERENCES course_grade_appeal_workflows(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_instances table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_instances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appeal_id INT NOT NULL,
    workflow_id INT NOT NULL,
    current_step INT NOT NULL,
    status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appeal_id) REFERENCES course_grade_appeals(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_id) REFERENCES course_grade_appeal_workflows(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_history table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    instance_id INT NOT NULL,
    step_id INT NOT NULL,
    user_id INT NOT NULL,
    action ENUM('started', 'completed', 'cancelled') NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instance_id) REFERENCES course_grade_appeal_workflow_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES course_grade_appeal_workflow_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_notifications table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    instance_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('step_started', 'step_completed', 'workflow_completed', 'workflow_cancelled') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instance_id) REFERENCES course_grade_appeal_workflow_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_steps table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_steps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    step_order INT NOT NULL,
    role ENUM('student', 'instructor', 'admin') NOT NULL,
    action ENUM('review', 'approve', 'reject', 'comment') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_notifications table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    step_id INT NOT NULL,
    type ENUM('email', 'in_app') NOT NULL,
    subject VARCHAR(255),
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES course_grade_appeal_workflow_template_steps(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_roles table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    role ENUM('student', 'instructor', 'admin') NOT NULL,
    can_start BOOLEAN DEFAULT FALSE,
    can_edit BOOLEAN DEFAULT FALSE,
    can_delete BOOLEAN DEFAULT FALSE,
    can_view BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_audit_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    action ENUM('created', 'updated', 'deleted', 'step_added', 'step_removed', 'step_updated') NOT NULL,
    details TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_role_permissions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    role ENUM('student', 'instructor', 'admin') NOT NULL,
    permission VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    step_id INT NOT NULL,
    condition_type ENUM('grade_threshold', 'time_limit', 'attempt_limit', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES course_grade_appeal_workflow_template_steps(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_actions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_actions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    step_id INT NOT NULL,
    action_type ENUM('notify', 'update_grade', 'create_ticket', 'custom') NOT NULL,
    action_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES course_grade_appeal_workflow_template_steps(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_rules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    step_id INT NOT NULL,
    rule_type ENUM('approval_required', 'rejection_required', 'comment_required', 'custom') NOT NULL,
    rule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES course_grade_appeal_workflow_template_steps(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notifications table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    step_id INT NOT NULL,
    notification_type ENUM('email', 'in_app', 'sms') NOT NULL,
    subject VARCHAR(255),
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES course_grade_appeal_workflow_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES course_grade_appeal_workflow_template_steps(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_recipients table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_recipients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    recipient_type ENUM('student', 'instructor', 'admin', 'custom') NOT NULL,
    recipient_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition_type ENUM('time_before', 'time_after', 'status_change', 'custom') NOT NULL,
    condition_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_attachments table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_logs table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    status ENUM('success', 'failed') NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_settings table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_templates table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    template_type ENUM('html', 'text', 'markdown') NOT NULL,
    template_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_variables table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_variables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    variable_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_schedules table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    schedule_type ENUM('immediate', 'delayed', 'recurring') NOT NULL,
    schedule_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES course_grade_appeal_workflow_template_step_notifications(id) ON DELETE CASCADE
);

-- Create course_grade_appeal_workflow_template_step_notification_conditions table if not exists
CREATE TABLE IF NOT EXISTS course_grade_appeal_workflow_template_step_notification_conditions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_id INT NOT NULL,
    condition