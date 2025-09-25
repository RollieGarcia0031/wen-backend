# Student-Porfessor Booking API

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

#### `POST /auth/signup`

Authenticates a user and logs them in.

*   **Method**: `POST`
*   **Body**:
    ```json
      {
        "name": "rollie",
        "email": "rollie@email.com",
        "password": "123456",
        "role":"professor | student"
      }
    ```
*   **Response (Inferred)**:
*   **Sucess**:
    ```json
      {
        "success": true,
        "message": "Signup successful",
        "data": {
          "id": "1",
          "email": "rollie@email.com",
          "name": "rollie"
        }
      }
    ```
*  **Fail**
    ```json
    {
      "success": false,
      "message": "Signup failed",
      "data":
        "SQLSTATE[23505]: Unique violation: 7 ERROR:  duplicate key value violates unique constraint \"users_email_key\"\nDETAIL:  Key (email)=(rollie@email.com) already exists."
    }
    ```

#### `POST auth/login`

Registers a new user.

*   **Method**: `POST`
*   **Body**:
    ```json
    {
      "email": "rollie@email.com",
      "password":"123456"
    }
    ```
*   **Response (Inferred)**:
    *   Success:
    ```json
      {
        "success": true,
        "message": "Login successful",
        "data": {
          "id": 1,
          "name": "rollie",
          "email": "rollie@email.com",
          "password": null,
          "role": "professor",
          "created_at": "2025-09-20 18:49:26.692636",
          "updated_at": "2025-09-20 18:49:26.692636"
        }
      }
    ```

#### `GET /auth/logout`

Logs out the current user.

*   **Method**: `GET`
*   **Body**: None
*   **Response (Inferred)**:
    *   Success: `{"success": true, "message": "Logged out successfully", "data": null}`
    *   Failure: `{"success": false, "message": "Not logged in", "data": null}`

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
    *   Success:
    ```json
    {
      "success": true,
      "message": "Profile added successfully",
      "data": {
        "id": "1"
      }
    }````
    *   Failure: `{"success": false, "message": "Error updating profile", "data": null}`

#### `POST /professor/availability`

Adds a new availability slot for a professor.

*   **Method**: `POST`
*   **Body**:
    ```json
    {
      "day": "Monday",
      "start": "09:00:00",
      "end": "10:00:00"
    }
    ```
*   **Response (Inferred)**:
    *   Success: 
    ```json
    {
      "success": true,
      "message": "Availability added successfully",
      "data": {
        "id": "1"
      }
    }
    ```
    *   Failure: `{"success": false, "message": "Error adding availability", "data": null}`

#### `GET /professor/availability`

Retrieves the availability of the currently logged-in professor.

*   **Method**: `GET`
*   **Body**: None
*   **Response (Inferred)**:
    *   Success:
    ```json
    {
      "success": true,
      "message": "Availability fetched",
      "data": [
        {
          "id": 1,
          "user_id": 1,
          "day_of_week": "Monday",
          "start_time": "09:00:00",
          "end_time": "10:30:00",
          "created_at": "2025-09-21 17:48:18.143076",
          "updated_at": "2025-09-21 17:48:18.143076"
        }
      ]
    }
    ```
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
    *   Success:
    ```json
    {
      "success": true,
      "message": "Professors found",
      "data": [
        {
          "prof_id": 1,
          "department": "CEN",
          "year": 1,
          "user_id": 1,
          "name": "rollie",
          "email": "rollie@email.com",
          "day_of_week": "Monday",
          "start_time": "09:00:00",
          "end_time": "10:30:00"
        }
      ]
    }
    ```
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
    *   Success:
    ```json
    {
      "success": true,
      "message": "Query Sucess",
      "data": {
        "role": "professor",
        "appointments": [
          {
            "appointment_id": 2,
            "student_id": 1,
            "professor_id": 1,
            "status": "pending",
            "message": "FROM CPE GE-II, for Emergency",
            "time_stamp": "2025-09-17 00:00:00",
            "name": "rollie",
            "day_of_week": "Monday",
            "start_time": "09:00:00",
            "end_time": "10:30:00"
          }
        ],
        "names": [
          {
            "id": 1,
            "name": "rollie"
          }
        ]
      }
    }
    ```
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

### 5. Get Currently Approved Appointment for Current Day
### GET /appointment/currentDayBooked
* **Method** `GET`

* **Response**:
  * Sucess:
  ```json
  {
    "success": true,
    "message": "Success",
    "data": [
      {
        "id": 4,
        "student_id": 1,
        "professor_id": 2,
        "availability_id": 3,
        "status": "confirmed",
        "message": "Eat",
        "time_stamp": "2025-09-21 00:00:00",
        "created_at": "2025-09-21 20:03:52.188579",
        "updated_at": "2025-09-21 20:03:52.188579",
        "name":"display name"
      }
    ]
  }
  ```


### 6. Counting Appointments
This is useful for loading counts of appointments in the dashboard
### GET /appointments/count
count of all of the appointments, that can be restricted by certain
status or time range.

  * **METHOD** `POST`
  * **BODY**:
  ```json
  {
    "status": "confirmed | pending", //optional, leave it blank to count the total of all
    "time_range": "past | today | tommorow | future|" //optional
  }
  ```

  * **RESPONSE**:
    * Sucess:
    ```json
    {
      "success": true,
      "message": "Fetched Appointments Count Sucessfully",
      "data": [
        {
          "total_appointments": 0
        }
      ]
    }
    ```

### POST /appointments/groupedCount
Returns a json containing count of appointments, restricted by a time range and grouped by category according to status

  * **METHOD** `POST`
  * **BODY**:
  ```json
  {
    "time_range": "past | today | tommorow | future|"
  }
  ```
  * **RESPONSE**:
    * Sucess:
    ```json
    {
      "success": true,
      "message": "Fetched Appointments Count Sucessfully",
      "data": [
        {
          "count": 1,
          "status": "pending"
        },
        {
          "count": 3,
          "status": "confirmed"
        }
      ]
    }
    ```

### 7. Error Handling

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