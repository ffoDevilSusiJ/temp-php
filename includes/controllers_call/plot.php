<?php

function controller_plot($act, $d) {
    if ($act == 'edit_window') return Plot::plot_edit_window($d);
    else if ($act == 'edit_update') return Plot::plot_edit_update($d);
    else if ($act == 'get_ids') return Plot::plots_ids($d);
    return '';
}
