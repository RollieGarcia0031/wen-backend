# Wen-Backend

This repository contains the backend for a web application designed to facilitate appointment scheduling and course management between students and professors.

## Features

*   **User Authentication:** Secure login and profile management for students and professors.
*   **Course Management:** Create, list, and manage courses.
*   **Appointment Scheduling:** (Implied, but not detailed in provided API) Functionality to schedule and manage appointments.

## API Endpoints

This section details the available API endpoints, including request and response formats.

### Authentication

#### Login User

`POST /auth/login`

Authenticates a user with their email and password.

**Request Body:**

```json
{
  "email": "rollie@email.com",
  "password": "123456"
}
```

**Response Body:**

```json
{
  "success": true,
  "message": "User logged in successfully",
  "data": {
    "id": 1,
    "name": "Rollie Angelo",
    "email": "rollie@email.com"
  }
}
```

#### Get User Profile

`GET /auth/profile`

Retrieves the profile information of the currently authenticated user.

**Request Headers:** (Assumed to require authentication token/cookie)

**Response Body:**

```json
{
  "success": true,
  "message": "User Logged",
  "data": {
    "id": 1,
    "name": "Rollie Angelo",
    "email": "rollie@email.com",
    "password": "*",
    "role": "student"
  }
}
```

### Course Management

#### Create Course

`POST /course/create`

Creates a new course.

**Request Body:**

```json
{
    "name": "CpE",
    "description": "Computer Engineering"
}
```

**Response Body:**

```json
{
    "success": true,
    "message": "Course Created",
    "data": {
        "id": 1
    }
}
```

#### List All Courses

`GET /course/list`

Retrieves a list of all available courses.

**Response Body:**

```json
{
    "success": true,
    "message": "Query Sucess",
    "data": [
        {
            "id": 6,
            "created_by": "Angelo",
            "name": "CpE",
            "description": "Computer Engineering"
        }
    ]
}
```

#### List Courses by User

`POST /course/user`

Retrieves courses associated with a specific user.

**Request Body:**

```json
{
    "user_id": "3"
}
```

**Response Body:**

```json
{
  "success": true,
  "message": "Query Success",
  "data": [
    {
      "id": 3,
      "year": 1,
      "name": "CpE",
      "description": "Computer Engineering"
    },
    {
      "id": 2,
      "year": 3,
      "name": "EE",
      "description": "ELECTRIC"
    }
  ]
}
```

## Setup

To get this project up and running, follow these steps:

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/wen-backend.git
    cd wen-backend
    ```

2.  **Install PHP Dependencies:**

    ```bash
    composer install
    ```

3.  **Database Setup:**
    *   Create a MySQL database.
    *   Update the database connection details in `.env.example` and rename it to `.env`.
    *   Run the SQL script to create necessary tables:
        ```bash
        mysql -u your_user -p your_database < src/Database/create_tables.sql
        ```

4.  **Web Server Configuration:**
    *   Configure your web server (e.g., Apache, Nginx) to point its document root to the `htdocs/` directory.
    *   Ensure URL rewriting is enabled to handle the API routes.

## Usage

Once the setup is complete, you can access the API endpoints through your configured web server. Use tools like Postman, Insomnia, or `curl` to send requests to the endpoints.

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for any bugs or feature requests.