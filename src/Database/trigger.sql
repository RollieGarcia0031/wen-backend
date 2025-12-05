/* ============================================================
   TRIGGER FUNCTIONS FOR ROLE VALIDATION
   ============================================================ */

-- FUNCTION: ensure only students insert into student_info
CREATE OR REPLACE FUNCTION check_student_role()
RETURNS TRIGGER AS $$
DECLARE
    user_role TEXT;
BEGIN
    SELECT role INTO user_role FROM users WHERE id = NEW.user_id;

    IF user_role <> 'student' THEN
        RAISE EXCEPTION 'Cannot insert into student_info: user % is not a student', NEW.user_id;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


-- FUNCTION: ensure only professors insert into professor_info
CREATE OR REPLACE FUNCTION check_professor_role()
RETURNS TRIGGER AS $$
DECLARE
    user_role TEXT;
BEGIN
    SELECT role INTO user_role FROM users WHERE id = NEW.user_id;

    IF user_role <> 'professor' THEN
        RAISE EXCEPTION 'Cannot insert into professor_info: user % is not a professor', NEW.user_id;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;



-- TRIGGER for student_info
DROP TRIGGER IF EXISTS trg_student_info_role_check ON student_info;
CREATE TRIGGER trg_student_info_role_check
BEFORE INSERT OR UPDATE ON student_info
FOR EACH ROW EXECUTE FUNCTION check_student_role();

-- TRIGGER for professor_info
DROP TRIGGER IF EXISTS trg_professor_info_role_check ON professor_info;
CREATE TRIGGER trg_professor_info_role_check
BEFORE INSERT OR UPDATE ON professor_info
FOR EACH ROW EXECUTE FUNCTION check_professor_role();