<?php if(!defined('SEC')){die('Forbidden path');}?>


<div style="text-align: center;">
<hr>
FOOTER
</div>
</div>
<script>
    var notificationsList = document.querySelector('.notifications-list');
    var notificationsIcon = document.querySelector('.icon');

    var isNotificationsOpen = false;

    function toggleNotifications() {
        if (isNotificationsOpen) {
            notificationsList.style.display = 'none';
            notificationsIcon.innerHTML = '🔔';
            isNotificationsOpen = false;
            history.pushState(null, document.title, window.location.pathname);
        } else {
            notificationsList.style.display = 'block';
            notificationsIcon.innerHTML = '✉️'; 
            isNotificationsOpen = true;
            history.pushState(null, document.title, window.location.pathname + '?notif=open');
        }
    }

    document.addEventListener('click', function(event) {
        var target = event.target;
        if (!target.classList.contains('icon') && !target.classList.contains('message')) {
            if (isNotificationsOpen) {
                notificationsList.style.display = 'none';
                notificationsIcon.innerHTML = '🔔';
                isNotificationsOpen = false;
                history.pushState(null, document.title, window.location.pathname);
            }
        }
    });
</script>

<script>
    document.querySelectorAll('.delete-button').forEach(function(button) {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        const postId = this.dataset.postId;
        const modal = document.getElementById(`deleteModal-${postId}`);
        modal.style.display = 'block';
    });
});

window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
});

function deletePost(url) {
    window.location.href = url;
}

function closeModal(postId) {
    const modal = document.getElementById(`deleteModal-${postId}`);
    modal.style.display = 'none';
}
</script>
</body></html>