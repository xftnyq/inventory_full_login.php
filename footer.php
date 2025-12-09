</div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('table:not(.table-sm)').DataTable({
            responsive: true
        });

        var currentUrl = window.location.pathname.split("/").pop();
        $('.sidebar-nav .nav-item').removeClass('active');
        $('.sidebar-nav .nav-link').each(function() {
            var linkHref = $(this).attr('href');
            if (linkHref == currentUrl) {
                $(this).closest('.nav-item').addClass('active');
            }
        });
    });
</script>
</body>
</html>