<?php

class User {

    // GENERAL

    public static function user_info($d) {
        // vars
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;
        $phone = isset($d['phone']) ? preg_replace('~\D+~', '', $d['phone']) : 0;
        // where
        if ($user_id) $where = "user_id='".$user_id."'";
        else if ($phone) $where = "phone='".$phone."'";
        else return [];
        // info
        $q = DB::query("SELECT user_id, phone, access FROM users WHERE ".$where." LIMIT 1;") or die (DB::error());
        if ($row = DB::fetch_row($q)) {
            return [
                'id' => (int) $row['user_id'],
                'access' => (int) $row['access']
            ];
        } else {
            return [
                'id' => 0,
                'access' => 0
            ];
        }
    }
    public static function users_list($d = []) {
        // vars
        $search = isset($d['search']) && trim($d['search']) ? $d['search'] : '';
        $selector = isset($d['selector']) && trim($d['selector']) ? $d['selector'] : '';
        $offset = isset($d['offset']) && is_numeric($d['offset']) ? $d['offset'] : 0;
        $limit = 20;
        $items = [];
        // where
        $where = [];
        //todo
        if ($search) $where[] = $selector ." LIKE '%".$search."%'";
        if (is_numeric($d)) $where[] = "user_id = " . $d;
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        // info Plot ID, First name, Last Name, Phone, Email, Last login
        $q = DB::query("SELECT plot_id, user_id, first_name, last_name, phone, email, last_login
            FROM users ".$where." ORDER BY user_id LIMIT ".$offset.", ".$limit.";") or die (DB::error());
        while ($row = DB::fetch_row($q)) {
            $items[] = [
                'user_id' => (int) $row['user_id'],
                'plot_id' => $row['plot_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'last_login' => date('Y/m/d', $row['last_login'])
            ];
        }
        // paginator
        $q = DB::query("SELECT count(*) FROM users ".$where.";");
        $count = ($row = DB::fetch_row($q)) ? $row['count(*)'] : 0;
        $url = 'users?';
        if ($search) $url .= '&search='.$search;
        paginator($count, $offset, $limit, $url, $paginator);
        // output
        return ['items' => $items, 'paginator' => $paginator];
    }

    public static function users_fetch($d = []) {
        $info = User::users_list($d);
        HTML::assign('users', $info['items']);
        return ['html' => HTML::fetch('./partials/users_table.html'), 'paginator' => $info['paginator']];
    }


    public static function users_list_plots($number) {
        // vars
        $items = [];
        // info
        $q = DB::query("SELECT user_id, plot_id, first_name, email, phone
            FROM users WHERE plot_id LIKE '%".$number."%' ORDER BY user_id;") or die (DB::error());
        while ($row = DB::fetch_row($q)) {
            $plot_ids = explode(',', $row['plot_id']);
            $val = false;
            foreach($plot_ids as $plot_id) if ($plot_id == $number) $val = true;
            if ($val) $items[] = [
                'id' => (int) $row['user_id'],
                'first_name' => $row['first_name'],
                'email' => $row['email'],
                'phone_str' => phone_formatting($row['phone'])
            ];
        }
        // output
        return $items;
    }


    public static function user_edit_window($d = []) {
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;
        HTML::assign('user', User::users_list($user_id)['items'][0]);
        HTML::assign('plots', Plot::plots_ids());
        return ['html' => HTML::fetch('./partials/user_edit.html')];
    }
    public static function user_edit_delete($d = []) { 
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;
        $offset = isset($d['offset']) ? preg_replace('~\D+~', '', $d['offset']) : 0;
        if ($user_id) {
            DB::query("DELETE FROM users WHERE user_id='".$user_id."' LIMIT 1;") or die (DB::error());
        }
        return User::users_fetch(['offset' => $offset]);
    }

    public static function user_edit_update($d = []) {
        // vars
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;
        $phone = isset($d['phone']) && is_numeric($d['phone']) ? $d['phone'] : 0;
        $first_name = isset($d['first_name']) && trim($d['first_name']) ? trim($d['first_name']) : '';
        $last_name = isset($d['last_name']) && trim($d['last_name']) ? trim($d['last_name']) : '';
        $email = isset($d['email']) && trim($d['email']) ? trim($d['email']) : '';
        $plot_ids = isset($d['plot_id']) && trim($d['plot_id']) ? trim($d['plot_id']) : '';
        $phone_code = isset($d['phone_code'])  && is_numeric($d['phone_code']) ? $d['phone_code'] : 1111;
        $offset = isset($d['offset']) ? preg_replace('~\D+~', '', $d['offset']) : 0;

        if($ex_plots = Plot::plots_ids(explode(',',$plot_ids)))
            $plot_ids = implode(",", $ex_plots);
        // update
        if ($user_id) {
            $set = [];
            $set[] = "phone='".$phone."'";
            $set[] = "first_name='".$first_name."'";
            $set[] = "last_name='".$last_name."'";
            $set[] = "email='".$email."'";
            $set[] = "phone_code='".$phone_code."'";
            $set[] = "plot_id='".$plot_ids."'";
            $set = implode(", ", $set);
            DB::query("UPDATE users SET ".$set." WHERE user_id='".$user_id."' LIMIT 1;") or die (DB::error());
        } else {
         
            DB::query("INSERT INTO users (
                phone,
                first_name,
                last_name,
                phone_code,
                email,
                plot_id
            ) VALUES (
                '".$phone."',
                '".$first_name."',
                '".$last_name."',
                '".$phone_code."',
                '".$email."',
                '".$plot_ids."'
            );") or die (DB::error());
           
        }
        // output
        return User::users_fetch(['offset' => $offset]);
    }

}
