# Backend REST API Route Analysis

This document summarizes all API endpoints defined in `src/Web/Route.php` and explains what each endpoint does based on its mapped controller method and controller documentation comments.

## Notes

- Base route map source: `src/Web/Route.php`.
- Role requirements and required fields are inferred from each controller method.
- Most endpoints return JSON responses with success/error metadata.
- ⚠️ `POST /section/enroll` is mapped to `SectionController::enrollStudent`, but the current controller defines `join()` (which performs enroll behavior) and does **not** define `enrollStudent()`.

---

## POST Endpoints

| Endpoint | Controller Method | Auth | Role | Required Fields | Brief Explanation |
|---|---|---|---|---|---|
| `/auth/register` | `AuthController::register` | No | Any | `name`, `email`, `password`, `role` | Creates a new user account. |
| `/auth/login` | `AuthController::login` | No | Any | `email`, `password` | Authenticates a user and creates a session. |
| `/auth/logout` | `AuthController::logout` | Session expected | Any | None | Logs out the current user by destroying session state. |
| `/course/create` | `CourseController::create` | Yes | Authenticated user | `name`, `description` | Creates a new course owned by the logged user. |
| `/course/use` | `CourseController::assignToUser` | Yes | Authenticated user | `year`, `course_id` | Assigns an existing course to the logged user. |
| `/course/unuse` | `CourseController::unuse` | Yes | Authenticated user | `course_id` | Removes an assigned/enrolled course from the logged user. |
| `/course/search` | `CourseController::search` | No | Any | `name` | Searches courses by name. |
| `/course/user` | `CourseController::findUser` | No | Any | `user_id` | Retrieves courses associated with a target user. |
| `/section/enroll` | `SectionController::enrollStudent` (mapped) | Yes | Authenticated user | `section_id` (from implemented enroll logic) | Intended to enroll current user into a section; mapping appears inconsistent with controller method names. |
| `/section/unenroll` | `SectionController::unenrollUser` | Yes | Authenticated user | `section_id` | Unenrolls current user from a section. |
| `/section/enroll/all` | `SectionController::enrollAll` | Yes | Authenticated user | `section_ids` | Enrolls current user into multiple sections in one request. |
| `/department/join` | `DepartmentController::join` | Yes | Professor | `department_id` | Adds logged professor to a department. |
| `/department/join/multi` | `DepartmentController::joinMulti` | Yes | Professor | `department_ids` | Adds logged professor to multiple departments. |
| `/department/leave` | `DepartmentController::leave` | Yes | Professor | `department_id` | Removes logged professor from a department. |
| `/availability/create` | `AvailabilityController::createNew` | Yes | Professor | `day`, `time_start`, `time_end` | Creates one availability slot for logged professor. |
| `/availability/createAll` | `AvailabilityController::createAll` | Yes | Professor | `availability_list` | Bulk-creates multiple availability slots. |
| `/availability/user` | `AvailabilityController::findUser` | Yes | Authenticated user | `user_id` | Gets availability list for a specific user. |
| `/appointment/send` | `AppointmentController::send` | Yes | Student | `availability_id`, `header`, `message`, `target_date` | Sends an appointment request to a professor slot. |
| `/appointment/list` | `AppointmentController::getOwnList` | Yes | Student or Professor | `cursor_id`, `cursor_date` | Returns paginated appointments for current user (sent for students, received for professors). |
| `/appointment/accept` | `AppointmentController::accept` | Yes | Professor | `id` | Approves a pending appointment. |
| `/appointment/decline` | `AppointmentController::decline` | Yes | Professor | `id` | Declines a pending appointment. |
| `/appointment/hide` | `AppointmentController::hide` | Yes | Professor | `ids` | Hides multiple appointments for professor view. |
| `/appointment/get/message` | `AppointmentController::getMessage` | Yes | Student or Professor | `id` | Retrieves the appointment message content, scoped to requesting user. |
| `/appointment/current-day` | `AppointmentController::currentDay` | Yes | Student or Professor | `cursor_id`, `cursor_time` | Returns paginated appointments for current day based on user role. |
| `/appointment/count` | `AppointmentController::count` | Yes | Student or Professor | `time_range` (`today`, `tomorrow`, `this_week`) | Returns appointment counts by status for a given range. |
| `/search/professors` | `SearchController::searchProfessor` | No | Any | `user_name` | Searches professors by name. |
| `/search/professor/user` | `SearchController::searchProfessorUser` | Yes | Student | `professor_user_id` | Gets detailed professor profile data for student view. |
| `/notification/list/unread` | `NotificationController::listUnread` | Yes | Authenticated user | `end_from` | Lists unread notifications for current user. |
| `/notification/list/all` | `NotificationController::listAll` | Yes | Authenticated user | `end_from` | Lists all notifications for current user. |
| `/info/update/student` | `InfoController::updateStudent` | Yes | Student | None strictly required (optional profile fields) | Updates logged student profile fields. |
| `/info/update/professor` | `InfoController::updateProfessor` | Yes | Professor | None strictly required (optional profile fields) | Updates logged professor profile fields. |
| `/info/professor` | `InfoController::getProfessor` | Yes | Authenticated user | `user_id` | Fetches full profile info for a target professor. |

---

## GET Endpoints

| Endpoint | Controller Method | Auth | Role | Required Fields | Brief Explanation |
|---|---|---|---|---|---|
| `/auth/profile` | `AuthController::getProfile` | Yes | Authenticated user | None | Returns the currently logged-in user/session profile. |
| `/course/list` | `CourseController::list` | No | Any | None | Returns all available courses. |
| `/course/list/self` | `CourseController::selfList` | Yes | Authenticated user | None | Returns courses created by the logged user. |
| `/course/assigned` | `CourseController::getAssigned` | Yes | Authenticated user | None | Returns courses assigned to logged user. |
| `/department/list/all` | `DepartmentController::listAll` | Yes | Professor | None | Lists all departments (professor-only endpoint). |
| `/department/list/joined` | `DepartmentController::listJoined` | Yes | Professor | None | Lists departments joined/owned by logged professor. |
| `/section/list/all` | `SectionController::getAll` | Yes | Authenticated user | None | Returns all sections available for enrollment. |
| `/section/list/owned` | `SectionController::getOwned` | Yes | Authenticated user | None | Returns sections associated with logged user. |
| `/availability/list` | `AvailabilityController::getOwnList` | Yes | Professor | None | Returns logged professor availability entries. |
| `/notification/count/unread` | `NotificationController::countUnread` | Yes | Authenticated user | None | Returns unread notification count for current user. |
| `/notification/mark-all-read` | `NotificationController::markAllAsRead` | Yes | Authenticated user | None | Marks all unread notifications as read. |
| `/info/professor` | `InfoController::professor` | Yes | Professor | None | Returns full profile info for logged professor. |
| `/info/student` | `InfoController::student` | Yes | Student | None | Returns full profile info for logged student. |

---

## DELETE Endpoints

| Endpoint | Controller Method | Auth | Role | Required Fields | Brief Explanation |
|---|---|---|---|---|---|
| `/course/delete` | `CourseController::delete` | Yes | Authenticated user | `id` | Deletes a course if owned by logged user. |
| `/availability/delete` | `AvailabilityController::delete` | Yes | Professor | `id` | Deletes one availability entry owned by logged professor. |
| `/appointment/delete` | `AppointmentController::delete` | Yes | Student | `id` | Deletes an appointment created by logged student. |
| `/notification/delete-all` | `NotificationController::deleteAll` | Yes | Authenticated user | None | Deletes all notifications belonging to logged user. |

---

## PUT Endpoints

| Endpoint | Controller Method | Auth | Role | Required Fields | Brief Explanation |
|---|---|---|---|---|---|
| `/appointment/message/update` | `AppointmentController::updateMessage` | Yes | Student | `id`, `message` | Updates message body of a student-owned appointment. |

---

## Quick Observations

1. `POST /section/enroll` appears to target a method name that does not exist in the current `SectionController` (`enrollStudent` vs available `join`).
2. Some search/list endpoints are public (`/course/list`, `/course/search`, `/course/user`, `/search/professors`) while many others require authentication; this is intentional per current middleware usage.
3. Pagination-like inputs are used in appointments (`cursor_id`, `cursor_date`, `cursor_time`) and notifications (`end_from`).
