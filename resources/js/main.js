/*******************************************************
                Accordion Sidebar Menu Start
*******************************************************/
var accItem = document.getElementsByClassName('accordionItem');
var accHD = document.getElementsByClassName('accordionItemHeading');
for (i = 0; i < accHD.length; i++) {
    accHD[i].addEventListener('click', toggleItem, false);
}

function toggleItem() {
    var itemClass = this.parentNode.className;

    for (i = 0; i < accItem.length; i++) {
        accItem[i].className = 'accordionItem closeIt';
    }
    if (itemClass == 'accordionItem closeIt') {
        this.parentNode.className = 'accordionItem openIt';
    }
}
/*******************************************************
                Accordion Sidebar Menu End
*******************************************************/

// Safety helper for class manipulation
function safeToggleClass(id, className, action) {
    var el = document.getElementById(id);
    if (el) {
        if (action === 'add') el.classList.add(className);
        else if (action === 'remove') el.classList.remove(className);
        else if (action === 'toggle') el.classList.toggle(className);
    }
}

/*******************************************************
            Toggle The Side Navigation Start
*******************************************************/
// document.getElementById("sidebarToggle").addEventListener("click", toggleSidebar);

var ts = document.getElementById('sidebarToggle');
if (ts) {
    ts.addEventListener("click", toggleSidebar);
}

function toggleSidebar() {
    var toggle = document.querySelector('body');
    if (toggle) toggle.classList.toggle('sidebar-toggled');
}

window.addEventListener("resize", resiz);
function resiz() {
    var element = document.querySelector('body');
    if (window.innerWidth <= 1024 && element) {
        element.classList.remove("sidebar-toggled");
    }
}
/*******************************************************
            Toggle The Side Navigation End
*******************************************************/

/*******************************************************
               Header More Filter Start
*******************************************************/
function openMoreFilter() {
    safeToggleClass("more_filter", "in", "add");
}

function closeMoreFilter() {
    safeToggleClass("more_filter", "in", "remove");
}

if ($('#more_filter').length > 0) {
    $(document).on('mouseup', function(e)
    {
        var container = $("#more_filter");
        var searchField = $(".bs-searchbox");
        var select2Field = $("#bs-select-2");
        var selectField = $(".bs-container");

        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0 && !searchField.is(e.target) && searchField.has(e.target).length === 0 && !select2Field.is(e.target) && select2Field.has(e.target).length === 0 && selectField.has(e.target).length === 0)
        {
            closeMoreFilter()
        }
    });
}


/*******************************************************
                Header More Filter End
*******************************************************/

/*******************************************************
                    Mobile Menu Start
*******************************************************/
function openMobileMenu() {
    safeToggleClass("mobile_menu_collapse", "toggled", "add");
    safeToggleClass("mobile_close_panel", "toggled", "add");
}

function closeMobileMenu() {
    safeToggleClass("mobile_menu_collapse", "toggled", "remove");
    safeToggleClass("mobile_close_panel", "toggled", "remove");
}
/*******************************************************
                    Mobile Menu End
*******************************************************/

/*******************************************************
              Mobile Admin Dashboard Open
*******************************************************/
function openAdminDashboard() {
    safeToggleClass("mob-admin-dash", "in", "add");
    safeToggleClass("close-admin-overlay", "in", "add");
}

var el_admin = document.getElementById('close-admin-overlay');
if (el_admin) {
    el_admin.addEventListener("click", closeAdminDashboard);
}

var el_admin2 = document.getElementById('close-admin');
if (el_admin2) {
    el_admin2.addEventListener("click", closeAdminDashboard);
}

function closeAdminDashboard() {
    safeToggleClass("mob-admin-dash", "in", "remove");
    safeToggleClass("close-admin-overlay", "in", "remove");
}
/*******************************************************
                    Mobile Settings End
*******************************************************/

/*******************************************************
                    Mobile Settings Open
*******************************************************/
function openSettingsSidebar() {
    safeToggleClass("mob-settings-sidebar", "in", "add");
    safeToggleClass("close-settings-overlay", "in", "add");
}

var el_settings = document.getElementById('close-settings');
if (el_settings) {
    el_settings.addEventListener("click", closeSettingsSidebar);
}

var el_settings_overlay = document.getElementById('close-settings-overlay');
if (el_settings_overlay) {
    el_settings_overlay.addEventListener("click", closeSettingsSidebar);
}

function closeSettingsSidebar() {
    safeToggleClass("mob-settings-sidebar", "in", "remove");
    safeToggleClass("close-settings-overlay", "in", "remove");
}
/*******************************************************
                    Mobile Settings End
*******************************************************/

/*******************************************************
                    Mobile Ticket Open
*******************************************************/
function openTicketsSidebar() {
    safeToggleClass("ticket-detail-contact", "in", "add");
    safeToggleClass("close-tickets-overlay", "in", "add");
}

var el_tickets = document.getElementById('close-tickets');
if (el_tickets) {
    el_tickets.addEventListener("click", closeTicketsSidebar);
}

var el_tickets_overlay = document.getElementById('close-tickets-overlay');
if (el_tickets_overlay) {
    el_tickets_overlay.addEventListener("click", closeTicketsSidebar);
}

function closeTicketsSidebar(){
    safeToggleClass("ticket-detail-contact", "in", "remove");
    safeToggleClass("close-tickets-overlay", "in", "remove");
}
/*******************************************************
                    Mobile Ticket End
*******************************************************/

/*******************************************************
                    Client Detail Open
*******************************************************/
function openClientDetailSidebar() {
    safeToggleClass("mob-client-detail", "in", "add");
    safeToggleClass("close-client-overlay", "in", "add");
    safeToggleClass("hide-project-menues", "in", "add");
}

var el_client_overlay = document.getElementById('close-client-overlay');
if (el_client_overlay) {
    el_client_overlay.addEventListener("click", closeClientDetail);
}

var el_client_detail = document.getElementById('close-client-detail');
if (el_client_detail) {
    el_client_detail.addEventListener("click", closeClientDetail);
}

function closeClientDetail() {
    safeToggleClass("mob-client-detail", "in", "remove");
    safeToggleClass("close-client-overlay", "in", "remove");
    safeToggleClass("hide-project-menues", "in", "remove");
}
/*******************************************************
                    Client Detail End
*******************************************************/

/*******************************************************
                    Project Menu Open
*******************************************************/
function openProjectSidebar() {
    safeToggleClass("mob-project-menu", "in", "add");
    safeToggleClass("close-project-overlay", "in", "add");
}

var el_proj_overlay = document.getElementById('close-project-overlay');
if (el_proj_overlay) {
    el_proj_overlay.addEventListener("click", closeProjectSidebar);
}

var el_proj = document.getElementById('close-projects');
if (el_proj) {
    el_proj.addEventListener("click", closeProjectSidebar);
}

function closeProjectSidebar() {
    safeToggleClass("mob-project-menu", "in", "remove");
    safeToggleClass("close-project-overlay", "in", "remove");
}
/*******************************************************
                    Project Menu End
*******************************************************/

/*******************************************************
                   Message Tabs Start
*******************************************************/
function msgTabs(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";

    var mcr = document.getElementById('msgContentRight');
    if (mcr) mcr.classList.add('d-block');
}

function closeMessageTab() {
    safeToggleClass("msgContentRight", "d-block", "remove");
}
/*******************************************************
                   Message Tabs End
*******************************************************/

/*******************************************************
                   RTL Start
*******************************************************/
function rtl() {
    safeToggleClass("body", "rtl", "toggle");
}
/*******************************************************
                   RTL End
*******************************************************/

/*******************************************************
                 Task Detail Start
*******************************************************/
function openTaskDetail() {
    safeToggleClass("task-detail-1", "in", "add");
    safeToggleClass("close-task-detail-overlay", "in", "add");
    safeToggleClass("close-task-detail", "in", "add");
}

var el_task_detail_overlay = document.getElementById('close-task-detail-overlay');
if (el_task_detail_overlay) {
    el_task_detail_overlay.addEventListener("click", closeTaskDetail);
}

var el_task_detail_close = document.getElementById('close-task-detail');
if (el_task_detail_close) {
    el_task_detail_close.addEventListener("click", closeTaskDetail);
}

function closeTaskDetail() {
    safeToggleClass("task-detail-1", "in", "remove");
    safeToggleClass("close-task-detail-overlay", "in", "remove");

	sessionStorage.setItem('RIGHT_MODAL', 'opened');

    if (window.history.length > 1) {
        window.history.back();
    }

    safeToggleClass("close-task-detail", "in", "remove");
}
/*******************************************************
                 Task Detail End
*******************************************************/




// $(document).ready(function () {

//     //Datatables
//     $('#example').DataTable({
//         "language": {
//             "paginate": {
//                 "next": '<i class="icon-arrow-right icons"></i>',
//                 "previous": '<i class="icon-arrow-left icons"></i>'
//             }
//         },
//         "paging": true,
//         "ordering": false,
//         "info": false
//     });

// })


