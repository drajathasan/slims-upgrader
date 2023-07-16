<div id="welcome" class="d-none flex-column justify-content-center align-items-center p-5" style="height: 100vh">
    <h3 class="font-weight-bold text-muted">Selamat Data di Peningkat! 👋</h3>
    <p style="font-size: 12pt">Sudah siap untuk mendapatkan fitur baru yang keren?</p>
    <div class="d-flex flex-row">
        <a href="<?= selfUrl(['page' => 'selectVersion']) ?>" class="upgraderLink notAJAX btn btn-primary">Hayuk, Gaskeun</a>&nbsp;
        <a href="<?= AWB ?>" class="btn btn-outline-secondary notAJAX">Ntar dulu ah</a>
    </div>
</div>
<script>
    let request = fetch('https://raw.githubusercontent.com/drajathasan/slims-upgrader-manifest/main/detail.json?v=<?= date('this') ?>')
    request
        .then((result) => result.json())
        .then((raw) => $('#welcome').removeClass('d-none').addClass('d-flex'))
        .catch((error) => {
            $('#welcome').html(`
            <h3 class="font-weight-bold text-muted">Yah, internet nya mati 😔</h3>
            <p style="font-size: 12pt">meningkatankan versi SLiMS membutuhkan akses internet,</p>
            `)
        })
</script>