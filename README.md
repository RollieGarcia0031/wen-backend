# Appointment Booking API

This document outlines the available API endpoints for the Appointment Booking system. All responses are in JSON format.

## Base URL

The API endpoints are relative to your server's base URL where `index.php` is accessible.

## General Response Structure

Most successful responses will follow a similar JSON structure:

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Relevant data for the specific endpoint
  }
}
```

Error responses will typically look like this:

```json
{
  "success": false,
  "message": "Error description",
  "data": null
}
```

## Endpoints

---

### 1. User Authentication

#### `POST /auth/login`

Authenticates a user and logs them in.

*   **Method**: `POST`
*   **Body**:
    ```json
    {
      "email": "user@example.com",
      "password": "yourpassword"
    }
    ```
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Login successful", "data": {"user_id": 123, "email": "user@example.com", ...}}`
    *   Failure: `{"success": false, "message": "Invalid credentials", "data": null}`

#### `POST /auth/signup`

Registers a new user.

*   **Method**: `POST`
*   **Body**:
    ```json
    {
      "name": "John Doe",
      "email": "john.doe@example.com",
      "password": "newpassword",
      "role": "student" // or "professor"
    }
    ```
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "User registered successfully", "data": {"user_id": 124, "email": "john.doe@example.com", ...}}`
    *   Failure: `{"success": false, "message": "Email already exists", "data": null}`

#### `GET /auth/logout`

Logs out the current user.

*   **Method**: `GET`
*   **Body**: None
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Logged out successfully", "data": null}`
    *   Failure: `{"success": false, "message": "Not logged in", "data": null}`

---

### 2. Professor Management

#### `POST /professor/profile`

Adds or updates a professor's profile details. This endpoint is likely for professors to set their academic year and department.

*   **Method**: `POST`
*   **Body**:
    ```json
    {
      "year": "2025",
      "department": "Computer Science"
    }
    ```
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Profile updated", "data": null}`
    *   Failure: `{"success": false, "message": "Error updating profile", "data": null}`

#### `POST /professor/availability`

Adds a new availability slot for a professor.

*   **Method**: `POST`
*   **Body**:
    ```json
    {
      "day": "Monday",
      "start": "09:00",
      "end": "10:00"
    }
    ```
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Availability added", "data": null}`
    *   Failure: `{"success": false, "message": "Error adding availability", "data": null}`

#### `GET /professor/availability`

Retrieves the availability of the currently logged-in professor.

*   **Method**: `GET`
*   **Body**: None
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Availability retrieved", "data": [{"day": "Monday", "start": "09:00", "end": "10:00"}, ...]}`
    *   Failure: `{"success": false, "message": "Not authorized or no availability found", "data": null}`

---

### 3. Search Functionality

#### `POST /search/professor`

Searches for professors based on various criteria.

*   **Method**: `POST`
*   **Body**: (All fields are optional)
    ```json
    {
      "name": "Dr. Smith",
      "day": "Wednesday",
      "time_start": "14:00",
      "time_end": "16:00",
      "department": "Physics",
      "year": "2024"
    }
    ```
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Professors found", "data": [{"id": 1, "name": "Dr. Smith", "department": "Physics", ...}, ...]}`
    *   Failure: `{"success": false, "message": "No professors found matching criteria", "data": null}`

#### `POST /search/availability`

Retrieves the availability for a specific professor by their ID.

*   **Method**: `POST`
*   **Body**:
    ```json
    {
      "id": 123 // Professor ID
    }
    ```
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Availability retrieved", "data": [{"day": "Monday", "start": "09:00", "end": "10:00"}, ...]}`
    *   Failure: `{"success": false, "message": "Professor not found or no availability", "data": null}`

---

### 4. Appointment Management

#### `POST /appointment/send`

Sends an appointment request to a professor.

*   **Method**: `POST`
*   **Body**:
    ```json
    {
      "prof_id": 123, // Professor ID
      "time_stamp": "2025-09-01 10:00:00" // Desired appointment time
    }
    ```
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Appointment request sent", "data": null}`
    *   Failure: `{"success": false, "message": "Error sending request (e.g., slot unavailable)", "data": null}`

#### `GET /appointment/list`

Lists all appointments for the current user (student or professor).

*   **Method**: `GET`
*   **Body**: None
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Appointments retrieved", "data": [{"id": 1, "prof_id": 123, "student_id": 456, "time": "2025-09-01 10:00:00", "status": "pending"}, ...]}`
    *   Failure: `{"success": false, "message": "No appointments found", "data": null}`

#### `POST /appointment/accept`

Accepts a pending appointment request. This is likely for professors.

*   **Method**: `POST`
*   **Body**:
    ```json
    {
      "id": 1 // Appointment ID
    }
    ```
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Appointment accepted", "data": null}`
    *   Failure: `{"success": false, "message": "Error accepting appointment (e.g., not found, not pending)", "data": null}`

---

### 5. Error Handling

#### Default (404 Not Found)

If an unknown URI is accessed, the API will return a 404 Not Found response.

*   **Method**: Any
*   **Endpoint**: Any undefined URI
*   **Response**:
    ```json
    {
      "success": false,
      "message": "Not found",
      "data": null
    }
    ```