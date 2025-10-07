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
CREATE TABLE appointments (
    id SERIAL PRIMARY KEY,
    availability_id INTEGER REFERENCES availability(id),
    status VARCHAR(50) DEFAULT 'pending',
    message TEXT,
    time_stamp TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    student_user_id INTEGER REFERENCES users(id),
    professor_user_id INTEGER REFERENCES users(id)
);

-- =========================
-- NOTIFICATIONS
-- =========================
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
    notification_id BIGINT NOT NULL REFERENCES notifications(id),
    CONSTRAINT user_notifications_user_id_unique UNIQUE (user_id),
    CONSTRAINT user_notifications_notification_id_unique UNIQUE (notification_id)
);

-- =========================
-- COURSES
-- =========================
CREATE TABLE courses (
    id SERIAL PRIMARY KEY,
    created_by INTEGER NOT NULL UNIQUE REFERENCES users(id),
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    new_column BIGINT NOT NULL
);

-- =========================
-- USER CLASS
-- =========================
CREATE TABLE user_class (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE REFERENCES users(id),
    course_id INTEGER NOT NULL REFERENCES courses(id),
    year SMALLINT NOT NULL
);
