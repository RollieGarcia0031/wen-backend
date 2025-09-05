= Appointment Scheduler Database Schema
Rollie Garcia
== Users Table

[source,sql]
----
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) CHECK (role IN ('student','professor')) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
----

== Professors Table

[source,sql]
----
CREATE TABLE professors (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    department VARCHAR(100),
    year INT CHECK (year IN (1,2,3,4)),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
----

== Availability Table

[source,sql]
----
CREATE TYPE day_of_week AS ENUM ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

CREATE TABLE availability (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    day_of_week day_of_week NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
----

== Appointments Table

[source,sql]
----
CREATE TYPE appointment_status AS ENUM ('pending', 'confirmed', 'canceled');

CREATE TABLE appointments (
id SERIAL PRIMARY KEY,
student_id INT REFERENCES users(id) ON DELETE CASCADE,
professor_id INT REFERENCES users(id) ON DELETE CASCADE,
availability_id INT REFERENCES availability(id) ON DELETE CASCADE,
status appointment_status DEFAULT 'pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
----

== Refresh Tokens Table

[source,sql]
----
CREATE TABLE refresh_tokens (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
----

