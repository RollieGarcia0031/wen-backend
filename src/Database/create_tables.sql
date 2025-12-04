DROP TABLE IF EXISTS professor_departments CASCADE;
DROP TABLE IF EXISTS professor_sections CASCADE;
DROP TABLE IF EXISTS student_sections CASCADE;
DROP TABLE IF EXISTS sections CASCADE;
DROP TABLE IF EXISTS courses CASCADE;
DROP TABLE IF EXISTS user_notifications CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS appointments CASCADE;
DROP TABLE IF EXISTS availability CASCADE;
DROP TABLE IF EXISTS departments CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- =========================
-- USERS
-- =========================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL CHECK (role IN ('student', 'professor')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- AVAILABILITY
-- =========================
CREATE TABLE availability (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    day_of_week INTEGER NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- APPOINTMENTS
-- =========================
-- status representation:
-- 0: pending
-- 1: approved
-- 3: declined
CREATE TABLE appointments (
    id SERIAL PRIMARY KEY,
    availability_id INTEGER REFERENCES availability(id) ON DELETE CASCADE,
    status SMALLINT,
    header VARCHAR(50),
    message TEXT,
    target_date DATE NOT NULL,
    visible_to_prof BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    student_user_id INTEGER REFERENCES users(id)
);

-- =========================
-- NOTIFICATIONS
-- =========================
-- each notifications has a level indicating its importance
-- also, message is limited to 255 characters
-- a state ranging from 0 (unread) to 1 (read) is indicated
CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    message VARCHAR(255) NOT NULL,
    level SMALLINT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- USER NOTIFICATIONS
-- =========================
CREATE TABLE user_notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id),
    status SMALLINT NOT NULL,
    notification_id BIGINT NOT NULL REFERENCES notifications(id)
);

-- =========================
-- COURSES
-- =========================
-- courses that can be made by admin only
CREATE TABLE courses (
    course_id SERIAL PRIMARY KEY,
    course_code VARCHAR(10) NOT NULL UNIQUE,
    course_name VARCHAR(100) NOT NULL
);

-- =========================
-- SECTIONS
-- =========================
-- sections belong to a course
CREATE TABLE sections (
    section_id SERIAL PRIMARY KEY,
    course_id INT NOT NULL REFERENCES courses(course_id),
    section_code VARCHAR(20) NOT NULL,
    year_level INT NULL,
    UNIQUE (section_code, year_level)
);

-- =========================
-- STUDENT SECTIONS
-- =========================
-- mapping table between students and sections
-- a student can be in many sections and a section can have many students
-- can only be created by students themselves
CREATE TABLE student_sections(
    user_id INT NOT NULL,
    section_id INT NOT NULL,
    UNIQUE (user_id, section_id)
);

-- =========================
-- PROFESSOR SECTIONS
-- =========================
-- mapping table between professors and sections
-- a professor can teach many sections and a section can have many professors
-- can only be created by professors themselves
CREATE TABLE professor_sections(
    user_id INT NOT NULL,
    section_id INT NOT NULL,
    UNIQUE (user_id, section_id)
);

-- =========================
-- DEPARTMENTS
-- =========================
-- departments that can be made by admin only
-- e.g., CAS, CEN, MED
CREATE TABLE departments (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE
);

-- =========================
-- PROFESSOR DEPARTMENTS
-- =========================
-- mapping table between professors and departments
-- a professor can belong to many departments and a department can have many professors
CREATE TABLE professor_departments (
    user_id INT UNIQUE NOT NULL,
    department_id INT NOT NULL
);

DROP TABLE IF EXISTS student_info CASCADE;
DROP TABLE IF EXISTS professor_info CASCADE;

CREATE TABLE student_info (
    user_id INTEGER PRIMARY KEY REFERENCES users(id),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    middle_name VARCHAR(100),
    birthday DATE
);

CREATE TABLE professor_info (
    user_id INTEGER PRIMARY KEY REFERENCES users(id),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    middle_name VARCHAR(100),
    birthday DATE,
    bio TEXT,
    gender INT CHECK (gender IN (1, 2, 3, 4)),
    cellphone_number VARCHAR(11)
);
