// Require jQuery normally & create global $ and jQuery variables
const $ = require('jquery');
global.$ = global.jQuery = $;

// Bootstrap
// Datatable (plugin jquery) pour bootstrap
import 'datatables.net/js/jquery.dataTables.js';
import 'datatables.net-bs5/js/dataTables.bootstrap5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';
$(document).ready(function() {
    // Datatable simple à partir du HTML
    $('.table-datatable').dataTable();
})
