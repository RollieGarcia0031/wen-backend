/**
 * Common API Response Structure
 */
export type ApiResponse<T = null> = {
    success: boolean;
    message: string;
    data: T;
};

/**
 * POST /auth/login
 */
export type LoginResponse = ApiResponse<{
    id: number;
    name: string;
    email: string;
    role: string;
} | null>;

/**
 * POST /auth/signup
 */
export type SignUpResponse = ApiResponse<{
    id: number;
    email: string;
    name: string;
} | null>;

/**
 * POST /auth/logout
 */
export type LogoutResponse = ApiResponse<null>;

/**
 * POST /professor/profile
 */
export type ProfessorProfileResponse = ApiResponse<{
    id: number;
} | null>;

/**
 * POST /professor/availability
 */
export type POSTProfessorAvailabilityResponse = ApiResponse<{
    id: number;
} | null>;

/**
 * GET /professor/availability
 */
export type GETProfessorAvailabilityResponse = ApiResponse<Array<{
    id: number;
    user_id: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    created_at: string;
}> | null>;

/**
 * POST /search/availability
 */
export type SearchAvailabilityResponse = ApiResponse<Array<{
    id: number;
    user_id: number;
    day_of_week: number;
    start_time: string;
    end_time: string;
    created_at: string;
}> | null>;

/**
 * POST /search/professor
 */
export type SearchProfessorResponse = ApiResponse<Array<{
    prof_id: number;
    department: string;
    year: number;
    user_id: number;
    name: string;
    email: string;
    day_of_week: number;
    start_time: string;
    end_time: string;
} | null> | null>;

/**
 * POST /appointment/send
 */
export type SendAppointmentResponse = ApiResponse<{
    appointmentId: number;
} | null>;

/**
 * GET /appointment/list
 */
export type GetAppointmentsResponse = ApiResponse<Array<{
    id: number;
    prof_id: number;
    time_stamp: string;
    status: string;
}> | null>;

/**
 * POST /appointment/accept
 */
export type ProfessorAcceptResponse = ApiResponse<null>;

/**
 * Generic Error Response (e.g., for 404 Not Found)
 */
export type ErrorResponse = {
    success: false;
    message: string;
    data: null;
};
