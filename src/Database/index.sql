/* ============================================================
   INDEXES
   ============================================================ */

-- USERS
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- AVAILABILITY
CREATE INDEX IF NOT EXISTS idx_availability_user_id ON availability(user_id);
CREATE INDEX IF NOT EXISTS idx_availability_day ON availability(day_of_week);
CREATE INDEX IF NOT EXISTS idx_availability_time_range 
    ON availability(start_time, end_time);

-- APPOINTMENTS
CREATE INDEX IF NOT EXISTS idx_appointments_availability_id 
    ON appointments(availability_id);
CREATE INDEX IF NOT EXISTS idx_appointments_student_user_id 
    ON appointments(student_user_id);
CREATE INDEX IF NOT EXISTS idx_appointments_status 
    ON appointments(status);
CREATE INDEX IF NOT EXISTS idx_appointments_target_date 
    ON appointments(target_date);
CREATE INDEX IF NOT EXISTS idx_appointments_professor_daily 
    ON appointments(target_date, availability_id);

-- NOTIFICATIONS
CREATE INDEX IF NOT EXISTS idx_notifications_level 
    ON notifications(level);
CREATE INDEX IF NOT EXISTS idx_notifications_created_at 
    ON notifications(created_at DESC);

-- USER NOTIFICATIONS
CREATE INDEX IF NOT EXISTS idx_user_notifications_user_id 
    ON user_notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_user_notifications_notification_id 
    ON user_notifications(notification_id);
CREATE INDEX IF NOT EXISTS idx_user_notifications_status 
    ON user_notifications(status);
CREATE INDEX IF NOT EXISTS idx_user_notifs_user_status 
    ON user_notifications(user_id, status);

-- SECTIONS
CREATE INDEX IF NOT EXISTS idx_sections_course_id 
    ON sections(course_id);
CREATE INDEX IF NOT EXISTS idx_sections_code_year 
    ON sections(section_code, year_level);

-- STUDENT SECTIONS
CREATE INDEX IF NOT EXISTS idx_student_sections_user_id 
    ON student_sections(user_id);
CREATE INDEX IF NOT EXISTS idx_student_sections_section_id 
    ON student_sections(section_id);

-- PROFESSOR SECTIONS
CREATE INDEX IF NOT EXISTS idx_professor_sections_user_id 
    ON professor_sections(user_id);
CREATE INDEX IF NOT EXISTS idx_professor_sections_section_id 
    ON professor_sections(section_id);

-- PROFESSOR DEPARTMENTS
CREATE INDEX IF NOT EXISTS idx_prof_dept_user_id 
    ON professor_departments(user_id);
CREATE INDEX IF NOT EXISTS idx_prof_dept_department_id 
    ON professor_departments(department_id);

-- STUDENT / PROFESSOR INFO
CREATE INDEX IF NOT EXISTS idx_student_info_lastname 
    ON student_info(last_name);
CREATE INDEX IF NOT EXISTS idx_professor_info_lastname 
    ON professor_info(last_name);
CREATE INDEX IF NOT EXISTS idx_professor_info_phone 
    ON professor_info(cellphone_number);