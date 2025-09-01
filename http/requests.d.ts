/**
 * POST /auth/signup
 */
export type SignUpRequest = {
    name: string;
    email: string;
    password: string;
    role: string;
};

/**
 * POST /auth/login
 */
export type LoginRequest = {
    email: string;
    password: string;
};

/**
 * POST /auth/logout
 */
export type LogoutRequest = null;

/**
 * POST /professor/profile
 */
export type ProfessorProfileRequest = {
    year: number;
    department: string;
};

/**
 * POST /professor/availability
 */
export type POSTProfessorAvailabilityRequest = {
    day: number;
    start: string;
    end: string;
};

/**
 * GET /professor/availability
 */
export type GETProfessorAvailabilityRequest = null;

/**
 * POST /search/availability
 */
export type GetAvailabilityRequest = {
    id: number;
};

/**
 * POST /search/professor
 */
export type SearchRequest = {
    name?: string;
    day?: number;
    time_start?: string;
    time_end?: string;
    department?: string;
    year?: number;
};

/**
 * POST /appointment/send
 */
export type SendAppointmentRequest = {
    prof_id: number;
    time_stamp: string;
};

/**
 * GET /appointment/list
 */
export type GetAppointmentsRequest = null;

/**
 * POST /appointment/accept
 * used by professor to accept a received appointment
 */
export type ProfessorAcceptRequest = {
    /**appointment id */
    id: number;
};