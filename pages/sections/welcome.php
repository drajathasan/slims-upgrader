<div id="welcome" class="d-flex flex-column justify-content-center align-items-center p-5" style="height: 100vh">
    <?php include __DIR__ . '/loading.php'; ?>
    <span style="font-size: 12pt">Tunggu Sebentar</span>
</div>
<script>
    let netCheck = async () => {
        try {
            let request = await fetch('<?= selfUrl(['check_internet' => 'yoi']) ?>')
            let response = await request.text()

            $('#welcome').html(response)
        } catch (error) {
            $('#welcome').html(`
                <h3 class="font-weight-bold text-muted">Yah, error 😔</h3>
                <p style="font-size: 12pt">${error},</p>
            `)
        }
    }

    netCheck()
</script>