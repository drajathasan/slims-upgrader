<div class="d-flex flex-column w-100 p-5">
    <h3 class="font-weight-bold text-muted">Versi yang mana?</h3>
    <p style="font-size: 12pt" class="my-2">Pengembangan SLiMS hadir dalam dua perilisan <strong><code>stabil</code></strong> dan <strong><code>pengembangan/develop</code></strong>.</p>
    <div class="w-100 card d-flex flex-row" style="width: 18rem;">
        <div class="card-body mx-2  col-6">
            <div class="d-flex w-full justiy-contents-between">
                <div class="col-6">
                    <h5 class="card-title">Stabil</h5>
                    <h6 id="stableVersion" class="card-subtitle mb-2 text-muted"></h6>
                </div>
                <div class="col-6 d-flex justify-content-end align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                    </svg>
                </div>
            </div>
            <p class="card-text my-3">Versi stabil merupakan rilis SLiMS yang sudah disiapkan sematang mungkin agar siap digunakan agar perpustakaan kamu dapat lebih asyik lagi 😊.</p>
            <a href="#" class="btn btn-outline-primary">Lanjut</a>
            <a href="#" class="card-link">Another link</a>
        </div>
        <div class="card-body mx-2  col-6">
            <div class="d-flex w-full justiy-contents-between">
                <div class="col-6">
                    <h5 class="card-title">Pengembangan</h5>
                    <h6 id="developVersion" class="card-subtitle mb-2 text-muted"></h6>
                </div>
                <div class="col-6 d-flex justify-content-end align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" fill="currentColor" class="bi bi-wrench-adjustable-circle" viewBox="0 0 16 16">
                        <path d="M12.496 8a4.491 4.491 0 0 1-1.703 3.526L9.497 8.5l2.959-1.11c.027.2.04.403.04.61Z"/>
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0Zm-1 0a7 7 0 1 0-13.202 3.249l1.988-1.657a4.5 4.5 0 0 1 7.537-4.623L7.497 6.5l1 2.5 1.333 3.11c-.56.251-1.18.39-1.833.39a4.49 4.49 0 0 1-1.592-.29L4.747 14.2A7 7 0 0 0 15 8Zm-8.295.139a.25.25 0 0 0-.288-.376l-1.5.5.159.474.808-.27-.595.894a.25.25 0 0 0 .287.376l.808-.27-.595.894a.25.25 0 0 0 .287.376l1.5-.5-.159-.474-.808.27.596-.894a.25.25 0 0 0-.288-.376l-.808.27.596-.894Z"/>
                    </svg>
                </div>
            </div>
            <p class="card-text my-3">Namun, apabila kamu ingin mengekplorasi fitur-fitur baru yang akan rilis pada versi stabil berikutnya kamu bisa pakai versi pengembangan/develop, dengan catatan bahwa kamu siap untuk tidak panik jika menemukan galat ya 😁.</p>      
            <a href="#" class="btn btn-outline-primary">Lanjut</a>
            <a href="#" class="card-link">Another link</a>
        </div>
    </div>
</div>
<script>
    try {
        $.getJSON('https://raw.githubusercontent.com/drajathasan/slims-upgrader-manifest/main/detail.json', function(item){
            $('#stableVersion').html(`<strong><code>${item.release.stable}</code></strong>`)
            $('#developVersion').html(`<strong><code>${item.release.develop}</code></strong>`)
        })
    } catch (error) {
        console.log(error)
    }
</script>