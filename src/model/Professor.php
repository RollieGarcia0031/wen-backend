<?php

require_once __DIR__ . '/AppModel.php';

class Professor extends AppModel {
    /**
     * pass null if you want to check the current user if its professor
     * pass id if you want to check another user
     */
    public function isVerified($uid=null){
        $id = $uid ?? $_SESSION['uid'];
        $stment = $this->db->prepare("SELECT role from users WHERE id = ?");
        $stment->execute([$id]);
        $role = $stment->fetch(PDO::FETCH_ASSOC)['role'];

        return $role === 'professor';
    }

    public function addProfile(int $year, string $department, int $id) {
        if (!$this->isVerified()){
            $this->code = 401;
            $this->message = "User is not a professor";
            return false;
        }

        $sql = "INSERT INTO professors (user_id, year, department) VALUES (?, ?, ?)";

        $stment = $this->db->prepare($sql);
        $stment->execute([$id, $year, $department]);

        if (!$stment) {
            $this->code = 500;
            $this->message = "Error adding profile";
            return false;
        }
        
        $this->code = 200;
        $this->message = "Profile added successfully";
        $this->data = ['id' => $this->db->lastInsertId()];
        return true;
    }

    public function removeProfile(int $id, int $prof_id) {
        if (!$this->isVerified()){
            $this->code = 401;
            $this->message = "User is not a professor";
            return false;
        }

        $sql = "DELETE FROM professors WHERE user_id = ? AND id = ?";

        $stment = $this->db->prepare($sql);
        $stment->execute([$prof_id, $id]);

        $affectedRows = $stment->rowCount();

        if ($affectedRows === 0) {
            $this->code = 404;
            $this->message = "Profile not found";
            return false;
        }
        
        $this->code = 200;
        $this->message = "Profile removed successfully";
        return true;
    }

    public function addAvailability($userId, $day, $start, $end){
        if (!$this->isVerified(null)){
            $this->code = 401;
            $this->message = "User is not a professor";
            return false;
        }
        $query = "INSERT INTO availability (user_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)";
        $stment = $this->db->prepare($query);

        $stment->execute([$userId, $day, $start, $end]);

        if (!$stment) {
            $this->code = 500;
            $this->message = "Error adding availability";
            return false;
        }
        
        $this->code = 200;
        $this->message = "Availability added successfully";
        $this->data = ['id' => $this->db->lastInsertId()];
        return true;
    }

    public function getAvailability($id){
        $query = "SELECT * FROM availability WHERE user_id = ?";
        $stment = $this->db->prepare($query);
        $stment->execute([$id]);

        $result = $stment->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result){
            $this->code = 200;
            $this->message = "No availability found";
            return false;
        }
        $this->code = 200;
        $this->data = $result;
        $this->message = "Availability fetched";
        return true;
    }

    public function search($name=null, $day=null, $time_start=null, $time_end=null, $department=null, $year=null){
		$query = "SELECT
			p.id as prof_id,
			p.department,
			p.year,
            u.id as user_id,
			u.name,
			u.email,
            a.day_of_week,
            a.start_time,
            a.end_time
		FROM professors p
		JOIN users u ON u.id = p.user_id
		LEFT JOIN availability a ON a.user_id = u.id
		WHERE 1 = 1";

        $params = [];

        if($name != null){
            $query .= ' AND u.name LIKE :name';
            $params[':name'] = "%$name%";
        }
        if($day != null){
            $query .= ' AND a.day_of_week = :day';
            $params[':day'] = $day;
        }
        if($time_start != null){
            $query .= ' AND a.start_time <= :time_start';
            $params[':time_start'] = $time_start;
        }
        if($time_end != null){
            $query .= ' AND a.end_time >= :time_end';
            $params[':time_end'] = $time_end;
        }
        if($department != null){
            $query .= ' AND p.department = :department';
            $params[':department'] = $department;
        }
        if($year != null){
            $query .= ' AND p.year = :year';
            $params[':year'] = $year;
        }

        $stment = $this->db->prepare($query);
        $stment->execute($params);

        $result = $stment->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result){
            $this->code = 200;
            $this->message = "no users found";
            return true;
        }

        $this->code = 200;
        $this->data = $result;
        $this->message = "Professors found";
        return true;
    }

    public function getProfile($user_id){
        $query = "SELECT  department, year FROM professors WHERE user_id = ?";
        $stment = $this->db->prepare($query);
        $stment->execute([$user_id]);

        $result = $stment->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$result){
            $this->code = 200;
            $this->message = "Empty Profile";
            return true;
        }
        $this->code = 200;
        $this->data = $result;
        $this->message = "Profile fetched";
        return true;
    }

    /**
     * searches for professors information by ID
     * 
     * @param int $prof_id professor ID to search
     */
    public function getInfo($prof_id){
        $query = "SELECT
            u.name,
            ARRAY_AGG(DISTINCT u.email),
            ARRAY_AGG(p.department),
            ARRAY_AGG(p.year)
        FROM users u
        JOIN professors p ON u.id = p.user_id
        WHERE u.id = ?
        GROUP BY u.name
        ";

        $stment = $this->db->prepare($query);
        $stment->execute([$prof_id]);

        $result = $stment->fetchAll(PDO::FETCH_ASSOC);

        if(!$result){
            $this->code = 404;
            $this->message = "Prof not Found";
            return true;
        }

        $this->code = 200;
        $this->data = $result;
        $this->message = "Profile fetched";
        return true;

    }

}