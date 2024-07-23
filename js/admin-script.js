document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('.nav-tab');
    var tabContents = document.querySelectorAll('.cf7-storage-tab-content');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();

            tabs.forEach(function(t) { t.classList.remove('nav-tab-active'); });
            tab.classList.add('nav-tab-active');

            tabContents.forEach(function(content) { content.style.display = 'none'; });
            document.querySelector('#' + tab.dataset.tab).style.display = 'block';
        });
    });

    document.querySelector('.nav-tab').click();
});
