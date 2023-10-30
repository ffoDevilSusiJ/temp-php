<?php

function controller_user($act, $d) {
    if ($act == 'edit_window') return User::user_edit_window($d);
    else if ($act == 'edit_update') return User::user_edit_update($d);
    else if ($act == 'edit_delete') return User::user_edit_delete($d);
    return '';
}
